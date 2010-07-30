/*    
 *    Kloxo, Hosting Control Panel
 *
 *    Copyright (C) 2000-2009	LxLabs
 *    Copyright (C) 2009-2010	LxCenter
 *
 *    This program is free software: you can redistribute it and/or modify
 *    it under the terms of the GNU Affero General Public License as
 *    published by the Free Software Foundation, either version 3 of the
 *    License, or (at your option) any later version.
 *
 *    This program is distributed in the hope that it will be useful,
 *    but WITHOUT ANY WARRANTY; without even the implied warranty of
 *    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *    GNU Affero General Public License for more details.
 *
 *    You should have received a copy of the GNU Affero General Public License
 *    along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */ 

#include <stdio.h>
#include <stdlib.h>
#include <string.h>
#include <errno.h>
#include <wait.h>
#include <netdb.h>
#include <unistd.h>
#include <sys/types.h>
#include <sys/socket.h>
#include <netinet/in.h>
#include <arpa/inet.h>
#include <dirent.h>
#include <openssl/crypto.h>
#include <openssl/ssl.h>
#include <openssl/err.h>

#define RSA_SERVER_CERT "/usr/local/lxlabs/kloxo/file/backend.crt"
#define RSA_SERVER_KEY  "/usr/local/lxlabs/kloxo/file/backend.key"

#define RSA_SERVER_CA_CERT "server_ca.crt"
#define RSA_SERVER_CA_PATH "sys$common:[syshlp.examples.ssl]"

#define MASTER 0
#define SLAVE  1
#define ON     1
#define OFF    0

#define RESTART_INTERVAL  60
#define SCAVENGE_INTERVAL 60
#define SISINFOC_INTERVAL 60 * 5 

#define MAX(x,y) if ((x) > (y)) return x; else return y;
#define RETURN_NULL(x) if ((x) == NULL) exit(1)
#define RETURN_ERR(err,s) if ((err) == -1) { perror(s); exit(1); }
#define RETURN_SSL(err) if ((err) == -1) { ERR_print_errors_fp(stderr); exit(1); }

int global_type;
static time_t restart_timer  = (time_t)0;
static time_t scavenge_timer = (time_t)0;
static time_t sisinfoc_timer = (time_t)0;

int run_php_prog_ssl(SSL *ssl, int sock)
{
	char ftempname[BUFSIZ];
	char tmpname[BUFSIZ];
	char buf[BUFSIZ];
	int pid;
	int err;
	int n, p, totaln;
	int pipefd[2];
	FILE *fp;
	char *data = NULL;
	char *data1 = NULL;

	bzero(buf, sizeof(buf));
	while (1) {
		err = ssl_or_tcp_read(ssl, sock, buf, sizeof(buf) - 1);
		if (err <= 0) {
			printf("Got EOF\n");
			break;
		}
		buf[err] = '\0';
		if (data) {
			data1 = malloc(strlen(data) + strlen(buf) + 1);
			sprintf(data1, "%s%s", data, buf);
			data = data1;
		} else {
			data = malloc(strlen(buf) + 1);
			strcpy(data, buf);
		}

		if (strstr(data, "___...___")) {
			break;
		}
	}

	printf("Input %d %s\n", strlen(data), data);
	bzero(buf, sizeof(buf));
	//printf ("Received %d chars:'%s'\n", err, buf);

	strcpy(tmpname, "/tmp/lxlabs_backendXXXXXX");
	mkstemp(tmpname);
	fp = fopen(tmpname, "w");
	fwrite(data, strlen(data), 1, fp);
	fclose(fp);
	snprintf(ftempname, sizeof(ftempname), "--temp-input-file=%s", tmpname);
	pipe(pipefd);
	pid = fork();
	if (pid == 0) {
		dup2(pipefd[1], 1);
		close(pipefd[0]);
		execl("/usr/local/lxlabs/ext/php/php", "lxphp", "../bin/common/process_single.php", ftempname, NULL);
		printf("Exec failed\n");
		exit(0);
	} else {
		close(pipefd[1]);
		printf("Pipe %d\n", pipefd[0]);
		while (1) {
			n = read(pipefd[0], buf, sizeof(buf));
			totaln += n;
			if (n > 0) {
				p = ssl_or_tcp_write(ssl, sock, buf, n);
			} else {
				printf("Got %d\n\n", totaln);
				// Dummy Read... A Must
				while (1) {
					bzero(tmpname, sizeof(tmpname));
					p = ssl_or_tcp_read(ssl, sock, tmpname, sizeof(tmpname));
					printf("Got %s\n\n", tmpname);
					if (p <= 0) {
						break;
					}
				}
				break;
			}
		}
	}
	exit(0);
}

int ssl_or_tcp_write(SSL *ssl, int sock, char * buf, int n)
{
	if (sock) {
		return write(sock, buf, n);
	} else {
		return SSL_write(ssl, buf, n);
	}
}

int ssl_or_tcp_read(SSL *ssl, int sock, char * buf, int n)
{
	int p;

	if (sock) {
		p = read(sock, buf, n);
		printf("Read %d %s \n", p, buf);
	} else {
		p = SSL_read(ssl, buf, n);
	}
	return p;
}

SSL_CTX * ssl_init()
{
	int err;
	int verify_client = OFF; /* To verify a client certificate, set ON */
	size_t client_len;
	char *str;
	char buf[4096];
	SSL_CTX *ctx;
	SSL *ssl;
	SSL_METHOD *meth;
	X509 *client_cert = NULL;

	/*----------------------------------------------------------------*/
	/* Load encryption & hashing algorithms for the SSL program */
	SSL_library_init();

	/* Load the error strings for SSL & CRYPTO APIs */
	SSL_load_error_strings();

	/* Create a SSL_METHOD structure (choose a SSL/TLS protocol version) */
	meth = SSLv2_method();

	/* Create a SSL_CTX structure */
	ctx = SSL_CTX_new(meth);

	if (!ctx) {
		ERR_print_errors_fp(stderr);
		exit(1);
	}

	/* Load the server certificate into the SSL_CTX structure */
	if (SSL_CTX_use_certificate_file(ctx, RSA_SERVER_CERT, SSL_FILETYPE_PEM) <= 0) {
		ERR_print_errors_fp(stderr);
		exit(1);
	}

	/* Load the private-key corresponding to the server certificate */
	if (SSL_CTX_use_PrivateKey_file(ctx, RSA_SERVER_KEY, SSL_FILETYPE_PEM) <= 0) {
		ERR_print_errors_fp(stderr);
		exit(1);
	}

	/* Check if the server certificate and private-key matches */
	if (!SSL_CTX_check_private_key(ctx)) {
		fprintf(stderr,"Private key does not match the certificate public key\n");
		exit(1);
	}

	if(verify_client == ON) {
		/* Load the RSA CA certificate into the SSL_CTX structure */
		if (!SSL_CTX_load_verify_locations(ctx, RSA_SERVER_CA_CERT, NULL)) {
			ERR_print_errors_fp(stderr);
			exit(1);
		}

		/* Set to require peer (client) certificate verification */
		SSL_CTX_set_verify(ctx,SSL_VERIFY_PEER,NULL);

		/* Set the verification depth to 1 */
		SSL_CTX_set_verify_depth(ctx, 1);
	}

	return ctx;
}

char tcp_create_socket(short int s_port)
{
	/* Set up a TCP socket */
	int err;
	int listen_sock;
	int sock;
	struct sockaddr_in sa_serv;
	struct sockaddr_in sa_cli;

	listen_sock = socket(PF_INET, SOCK_STREAM, IPPROTO_TCP);

	RETURN_ERR(listen_sock, "socket");
	memset(&sa_serv, '\0', sizeof(sa_serv));
	sa_serv.sin_family = AF_INET;
	sa_serv.sin_addr.s_addr = INADDR_ANY;
	sa_serv.sin_port = htons(s_port); /* Server Port number */
	err = bind(listen_sock, (struct sockaddr*)&sa_serv, sizeof(sa_serv));
	RETURN_ERR(err, "bind");
	err = listen(listen_sock, 500000);
	return listen_sock;
}

char* ssl_sock_read(int sock, SSL_CTX *ctx)
{
	int err;
	int verify_client = OFF; /* To verify a client certificate, set ON */
	int pid;
	struct sockaddr_in sa_serv;
	struct sockaddr_in sa_cli;
	size_t client_len;
	char *str;
	char buf[4096];
	SSL *ssl;
	SSL_METHOD *meth;
	X509 *client_cert = NULL;

	/* ----------------------------------------------- */
	/* TCP connection is ready. */
	/* A SSL structure is created */
	ssl = SSL_new(ctx);

	RETURN_NULL(ssl);

	/* Assign the socket into the SSL structure (SSL and socket without BIO) */
	SSL_set_fd(ssl, sock);

	/* Perform SSL Handshake on the SSL server */
	err = SSL_accept(ssl);

	RETURN_SSL(err);

	/* Informational output (optional) */
	//printf("SSL connection using %s\n", SSL_get_cipher (ssl));

	if (verify_client == ON) {
		/* Get the client's certificate (optional) */
		client_cert = SSL_get_peer_certificate(ssl);
		if (client_cert != NULL) {
			//printf ("Client certificate:\n");
			str = X509_NAME_oneline(X509_get_subject_name(client_cert), 0, 0);
			RETURN_NULL(str);
			//printf ("\t subject: %s\n", str);
			free(str);
			str = X509_NAME_oneline(X509_get_issuer_name(client_cert), 0, 0);
			RETURN_NULL(str);
			//printf ("\t issuer: %s\n", str);
			free(str);
			X509_free(client_cert);
		} else {
			printf("The SSL client does not have certificate.\n");
		}
	}

	/*------- DATA EXCHANGE - Receive message and send reply. -------*/
	/* Receive data from the SSL client */
	/* Send data to the SSL client */

	run_php_prog_ssl(ssl, 0);

	/*--------------- SSL closure ---------------*/
	/* Shutdown this side (server) of the connection. */
	err = SSL_shutdown(ssl);
	RETURN_SSL(err);
	/* Terminate communication on a socket */
	err = close(sock);
	RETURN_ERR(err, "close");
	/* Free the SSL structure */
	//SSL_free(ssl);
	/* Free the SSL_CTX structure */
	//SSL_CTX_free(ctx);
	exit(0);
}

int tcp_sock_read(int sock)
{
	/* Wait for an incoming TCP connection. */
	run_php_prog_ssl(NULL, sock);
	close(sock);
	exit(0);
}

int accept_and(int listen_sock)
{
	int sock;
	struct sockaddr_in sa_cli;
	size_t client_len;

	client_len = sizeof(sa_cli);
	/* Socket for a TCP/IP connection is created */
	sock = accept(listen_sock, (struct sockaddr*)&sa_cli, &client_len);
	//printf ("Connection from %lx, port %x\n", sa_cli.sin_addr.s_addr, sa_cli.sin_port);
	return sock;
}

ssl_or_tcp_fork(int listen_socket, SSL_CTX *ctx)
{
	int pid;
	int sock;

	sock = accept_and(listen_socket);
	pid = fork();
	if (pid == 0) {
		close(listen_socket);
		if (ctx) {
			ssl_sock_read(sock, ctx);
		} else {
			tcp_sock_read(sock);
		}
		exit(0);
	}
	close(sock);
}

int close_and_system(char *cmd)
{
	int i, pid;

	pid = fork();
	if (pid == 0) {
		for (i = 3; i < 1024; i++) {
			close(i);
		}
		system(cmd);
		exit(9);
	}
}

int check_restart()
{
	struct dirent **namelist;
	char cmd[BUFSIZ];
	struct tm tms;
	time_t now;
	int n;
	int i;
	char *position, *neededstring;

	now = time(NULL);
	if (now - restart_timer < RESTART_INTERVAL) {
		return 1;
	}

	printf("Checking Restarts...\n");

	n = scandir("../etc/.restart/", &namelist, 0, alphasort);
	if (n < 0) {
		perror("scandir");
		return 1;
	}

	while(n--) {
		position = strstr(namelist[n]->d_name, "._restart_");
		if (position) {
			neededstring = position + 10;
			if (!strcmp(neededstring, "lxcollectquota")) {
				printf("Running CollectQuota\n");
				close_and_system("/usr/local/lxlabs/ext/php/php ../bin/collectquota.php --just-db=true &");
			} else if (!strcmp(neededstring, "openvz_tc")) {
				printf("Running Openvz\n");
				close_and_system("sh ../etc/openvz_tc.sh");
			} else {
				printf("Restarting %s\n", neededstring);
				snprintf(cmd, sizeof(cmd), "/etc/init.d/%s restart &", neededstring);
				close_and_system(cmd);
			}
			snprintf(cmd, sizeof(cmd), "../etc/.restart/%s", namelist[n]->d_name);
			unlink(cmd);
		}
		free(namelist[n]);
	}
	free(namelist);

	restart_timer = now;
}

int exec_sisinfoc()
{
	time_t now;

	now = time(NULL);
	if (now - sisinfoc_timer < SISINFOC_INTERVAL) {
		return 1;
	}

	printf("Executing Sisinfoc...\n");
	close_and_system("/usr/local/lxlabs/ext/php/php ../bin/sisinfoc.php >/dev/null 2>&1 &");

	sisinfoc_timer = now;
}

int exec_scavenge()
{
	int hour, min;
	int time_match;
	int interval;
	struct tm tms;
	time_t now;
	int i;
	FILE *fp;

	// Only for master
	if (global_type != MASTER) {
		return 1;
	}

	now = time(NULL);
	if (now - scavenge_timer < SCAVENGE_INTERVAL) {
		return 1;
	}

	hour = 3;
	min = 35;

	printf("Loading Scavenge time configuation...\n");

	if (!access("../etc/conf/scavenge_time.conf", R_OK)) {
		fp = fopen("../etc/conf/scavenge_time.conf", "r");
		if (fp) {
			fscanf(fp, "%d %d", &hour, &min);
			fclose(fp);
		}
	}

	localtime_r(&now, &tms);
	printf(" Now Value:  %02d:%02d\n", tms.tm_hour, tms.tm_min);
	printf(" Read Value: %02d:%02d\n", hour, min);

	// check interval of 5 minutes
	interval = 5;
	time_match = 0;
	for(i = 0; i <= interval; i++) {
		if (tms.tm_hour == hour && tms.tm_min == min) {
			time_match = 1;
			break;
		}
		min++;
		if (min == 60) {
			min = 0;
			hour = hour < 23 ? (hour + 1) : 0;
		}
	}

	if (time_match) {
		printf("Executing Scavenge...\n");
		close_and_system("/usr/local/lxlabs/ext/php/php ../bin/scavenge.php >/dev/null 2>&1 &");
		scavenge_timer = now + interval * 60;
	}
	else {
		scavenge_timer = now;
	}
}

int main(int argc, char **argv)
{
	int err;
	int pid;
	int sock;
	fd_set socklist; 
	int status;
	int ssl_sock, tcp_sock, max_sock;
	int select_ret;
	struct timeval tv;
	SSL_CTX *ctx;

	// disable stdout buffering
	setvbuf(stdout, NULL, _IONBF, 0);

	if (argc < 2) {
		printf("Usage: %s master|slave\n", argv[0]);
		exit(0);
	}

	if (!strcmp(argv[1], "master")) {
		global_type = MASTER;
	} else {
		global_type = SLAVE;
	}

	if (getuid() != 0) {
		printf("Not root user\n");
		exit(6);
	}

	exec_sisinfoc();

	ctx = ssl_init();

	ssl_sock = tcp_create_socket(7779);
	tcp_sock = tcp_create_socket(7776);

	while (1) {
		tv.tv_sec = 60;
		tv.tv_usec = 0;
		FD_ZERO(&socklist); /* Always clear the structure first. */
		FD_SET(ssl_sock, &socklist); 
		FD_SET(tcp_sock, &socklist); 

		max_sock = (tcp_sock > ssl_sock ? tcp_sock : ssl_sock);

		max_sock += 1;
		select_ret = select(max_sock, &socklist, NULL, NULL, &tv);
		if (select_ret == -1) {
			perror("Select");
			exit(0);
		}
		while (wait3(&status, WNOHANG, 0) > 0);

		check_restart();
		exec_sisinfoc();
		exec_scavenge();

		if (select_ret > 0) {
			if (FD_ISSET(tcp_sock, &socklist)) {
				//printf("TCP connection %d\n", select_ret);
				ssl_or_tcp_fork(tcp_sock, NULL);
			}
			if (FD_ISSET(ssl_sock, &socklist)) {
				//printf("SSl connection %d\n", select_ret);
				ssl_or_tcp_fork(ssl_sock, ctx);
			}
		}
	}
}

