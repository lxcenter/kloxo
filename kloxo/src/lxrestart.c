

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
#include <stdlib.h>


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
	char buf[BUFSIZ];

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
	if (argc < 2)  {
		exit(0);
	}

	setgid(0); setegid(0); setuid(0); seteuid(0);

	// Well OKay, escaping is not needed here as we want to run at
	// most two commands: kloxo/hypervm
	if(strlen(argv[1]) > strlen("hypervm") &&
	   !memcmp(argv[1], "hypervm", 7))
	{
		exit(0);
	} 
	if(strlen(argv[1]) > strlen("kloxo") &&
	   !memcmp(argv[1], "kloxo", 5))
	{
		exit(0);
	} 
	
	
	snprintf(buf, BUFSIZ - 1, "/etc/init.d/%s backendrestart", argv[1]);
	system(buf);
	//printf("this Shouldn't be\n");

}


