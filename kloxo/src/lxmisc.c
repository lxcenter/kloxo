/* 
 * This file seems not in use anymore
 * LxCenter, dterweij, march 17 2010
 */


/* Kloxo wrapper. Allows the php script - which runs as kloxo user to execute programs 
 * that need root 
 * previleges. In the production version, error messages should be completely avoided,
 * the program failing 
 * without giving any reason for the failure, so as to discourage the curious idiots.
 *
 */

#include <stdio.h>
#include <pwd.h>
#include <string.h>


int exec_command(int argc, char **argv)
{
	setenv("PATH", "/bin:/sbin:/usr/sbin:/usr/bin:/usr/local/bin:/usr/local/sbin", 1);
	execvp(argv[0], argv);
}


int main(int argc, char **argv)
{

	int uid, gid, suid, sgid, euid;
	char *username;
	char s[BUFSIZ];

	int debug = 0;

	// Setting it to large value initially. So that, 
	// if finding the uer was unscccesful, it doesn't default to root.
	uid = gid = suid = sgid = euid = 10001;
	struct passwd *pwd;
	uid = getuid();
	gid = getgid();
	pwd = getpwuid(uid);
	suid = sgid = 0;


	if (!pwd || (strcmp(pwd->pw_name, "lxlabs") && strcmp(pwd->pw_name, "root"))) {
// To be removed before deployment. The production version shouldn't print anything. 
// It should just exit without displaying any errors. This error message is only 
// for debugging purposes
		if (debug) {
			printf("%s: Not allowd to execute\n", pwd->pw_name);
		}
		exit(0);
	}


	// 3 arguments are expected. '-u' 'user', the command ... the rest are arguments.
	if (argc < 4)  {
		exit(2);
	}

	argc--;
	argv++;

	while(argv[0][0] == '-') {
		struct passwd *pw;
		if (argv[0][1] == 'u') {
			username = strdup(argv[1]);
			pw = getpwnam(username);
			if (pw) {
				suid = pw->pw_uid;
				sgid = pw->pw_gid;
				setgid(sgid); setegid(sgid); setuid(suid); seteuid(suid);
			} else {
				fprintf(stderr, "User %s Doesn't Exist...\n", username);
				exit(192);
			}

			/*
			if (suid == 0) {
				fprintf(stderr, "Cannot execute as root");
				exit(200);
			}
			*/
			setgid(sgid); setegid(sgid); setuid(suid); seteuid(suid);
		}

		argv+=2;
	}
	
	exec_command(argc, argv);
	//printf("this Shouldn't be\n");

}


