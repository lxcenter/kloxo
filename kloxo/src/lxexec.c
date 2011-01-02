#include <stdio.h>
#include <strings.h>
#include <string.h>
#include <unistd.h>
#include <sys/wait.h>
#include <signal.h>
#include <sys/types.h>
#include <stdlib.h>
#include <ctype.h>
#include <sys/stat.h>
#include <dirent.h>
#include <fcntl.h>
#include <time.h>

int main(int argc, char **argv)
{
	argc--;
	argv++;
	execvp(argv[0], argv);
}
