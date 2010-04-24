/*
 * Could someone confirm that this is the closeallinput binary?
 */
#include <stdio.h>

int main(int argc, char **argv)
{
	int i;

	for(i = 3; i< 1024; i++) {
		close(i);
	}

	if (argc > 1) {
		system(argv[1]);
	}
}
