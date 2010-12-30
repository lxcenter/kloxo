/*

Superuser-exec wrapper for HTTP serves and other needs (made especially for lighttpd).
Allows programs to be run with configurable uid/gid.
Version 0.4 (2006-06-09)

For documentation on how to configure the wrapper, see the README.
Command line option -v displays version, while -V displays compile-time configuration.
These options are only available for the super user and the server admin.

Brief version history:

Vers  Date        Changes
-----------------------------------------------------------------------------------------
0.4   2006-06-09  Added a BSD license.
0.3   2005-09-27  Changed the wrapper to stay resident and propagate SIGTERM to the
                  target. Not doing so will prevent the server from managing the target.
                  The old behaviour can be restored with a run-time option. Fixed a bug
                  in the call to execl, which could cause it to fail or crash. No
                  security risk. Added custom return codes on errors. Added some command
                  line options.
0.2   2005-09-14  Cleaned up source a bit, and added compile-time security checks. Fixed
                  a few minor issues. Compiles under C89 now (e.g. gcc -std=c89).
0.1   2005-09-08  Initial version.


License:

Copyright (c) 2006, Sune Foldager
All rights reserved.

Redistribution and use in source and binary forms, with or without modification, are
permitted provided that the following conditions are met:

- Redistributions of source code must retain the above copyright notice, this list of
  conditions and the following disclaimer.
- Redistributions in binary form must reproduce the above copyright notice, this list of
  conditions and the following disclaimer in the documentation and/or other materials
  provided with the distribution.

THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND ANY
EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF
MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL
THE COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT
OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION)
HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR
TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS
SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.

*/


/* Configuration. Shouldn't be changed. */

#define ENV_UID                 "MUID="
#define ENV_GID                 "GID="
#define ENV_TARGET              "TARGET="
#define ENV_CHECK_GID           "CHECK_GID="
#define ENV_NON_RESIDENT        "NON_RESIDENT="

/* Return values for various errors. */

#define RC_CALLER_UID           10
#define RC_TARGET_UID           11
#define RC_TARGET_GID           12
#define RC_TARGET               13
#define RC_MISSING_CONFIG       14
#define RC_SIGNAL_HANDLER       15
#define RC_SETGID               16
#define RC_SETUID               17
#define RC_STAT                 18
#define RC_WORLD_WRITE          19
#define RC_WRONG_USER           20
#define RC_GROUP_WRITE          21
#define RC_WRONG_GROUP          22
#define RC_EXEC                 23
#define RC_BAD_OPTION           24

/* User configuration. */
#include "lxsuexec.h"

/* Compile-time security checks. */
#if !TARGET_MIN_UID
#error SECURITY: TARGET_MIN_UID set to 0. See README for details.
#endif
#if !TARGET_MIN_GID
#error SECURITY: TARGET_MIN_GID set to 0. See README for details.
#endif
#if DEFAULT_UID < TARGET_MIN_UID
#error SECURITY: DEFAULT_UID set to a lower value than TARGET_MIN_UID. See README for details.
#endif
#if DEFAULT_GID < TARGET_MIN_GID
#error SECURITY: DEFAULT_GID set to a lower value than TARGET_MIN_GID. See README for details.
#endif

/* Useful macro and other stuff. */
#define VERSION_STRING "ExecWrap v0.3 by Sune Foldager."
#define STRLEN(a) (sizeof(a)-1)

/* Shortcuts. */
#define TARGET_PATH_PREFIX_LEN  STRLEN(TARGET_PATH_PREFIX)
#define ENV_UID_LEN             STRLEN(ENV_UID)
#define ENV_GID_LEN             STRLEN(ENV_GID)
#define ENV_TARGET_LEN          STRLEN(ENV_TARGET)
#define ENV_CHECK_GID_LEN       STRLEN(ENV_CHECK_GID)
#define ENV_NON_RESIDENT_LEN    STRLEN(ENV_NON_RESIDENT)

/* Stuff we need. */
#include <stdio.h>
#include <stdlib.h>
#include <string.h>
#include <sys/stat.h>
#include <unistd.h>
#include <signal.h>


/* The global child PID and previous SIGTERM handler. */
int pid;
void (*oldHandler)(int);


/* SIGTERM handler. */
void sigTermHandler(int signal)
{

  /* If we're in the parent, kill the child as well. */
  if(pid) kill(pid, SIGTERM);

  /* Call the default handler. */
  oldHandler(signal);

}


/* Down to business. */
int main(int argc, char* argv[], char* envp[])
{

  /* Verify parent UID. Only the super user and PARENT_UID are allowed. */
  if(getuid() != 0 && getuid() != PARENT_UID) return RC_CALLER_UID;

  /* Command line options. */
  if(argc > 1)
  {
    if(argv[1][0] == '-') switch(argv[1][1])
    {
      case 'v': puts(VERSION_STRING);
                return 0;
      case 'V': puts(VERSION_STRING);
                puts("Compile-time configuration:");
                printf("PARENT_UID         : %d\n", PARENT_UID);
                printf("TARGET_MIN_UID     : %d\n", TARGET_MIN_UID);
                printf("TARGET_MIN_GID     : %d\n", TARGET_MIN_GID);
                printf("TARGET_PATH_PREFIX : %s\n", TARGET_PATH_PREFIX);
                printf("DEFAULT_UID        : %d\n", DEFAULT_UID);
                printf("DEFAULT_GID        : %d\n", DEFAULT_GID);
                return 0;
    }

    /* Fail on unknown option. Known options return quietly above. */
    //return RC_BAD_OPTION;
  }

  /* Grab stuff from environment, and set defaults. */
  int uid = DEFAULT_UID;
  int gid = DEFAULT_GID;
  char* target = 0;
  char* args = 0;
  char check_gid = 0;
  char non_resident = 0;
  char** p = envp;
  char* s;
  char* t;
  while(s = *p++)
  {

    /* Target UID. */
    if(!strncmp(ENV_UID, s, ENV_UID_LEN))
    {
      uid = atoi(s + ENV_UID_LEN);
      if(uid < TARGET_MIN_UID) return RC_TARGET_UID;
    }

    /* Target GID. */
    if(!strncmp(ENV_GID, s, ENV_GID_LEN))
    {
      gid = atoi(s + ENV_GID_LEN);
      if(gid < TARGET_MIN_GID) return RC_TARGET_GID;
    }

    /* Target script. */
    if(!strncmp(ENV_TARGET, s, ENV_TARGET_LEN))
    {
      target = s + ENV_TARGET_LEN;
      if((target[0] != '/') || strchr(target, '~') || strstr(target, "..") ||
        strncmp(TARGET_PATH_PREFIX, target, TARGET_PATH_PREFIX_LEN)) return RC_TARGET;
    }

    /* Check GID instead of UID. */
    if(!strncmp(ENV_CHECK_GID, s, ENV_CHECK_GID_LEN))
    {
      check_gid = 1;
    }

    /* Use non-resident wrapping style. */
    if(!strncmp(ENV_NON_RESIDENT, s, ENV_NON_RESIDENT_LEN))
    {
      non_resident = 1;
    }

  }

  /* See if we got all we need. */
  if(!target) return RC_MISSING_CONFIG;

  /* Install the SIGTERM handler. */
  if(!non_resident)
  {
    oldHandler = signal(SIGTERM, sigTermHandler);
    if(oldHandler == SIG_ERR) return RC_SIGNAL_HANDLER;
  }

  /* Fork off (or, if we are a non-resident wrapper, just carry on). */
  if(non_resident || !(pid = fork()))
  {

    /* We're in the child. Drop privileges. */
	setgroups(0, NULL);
    if(setgid(gid)) return RC_SETGID;
    if(setuid(uid)) return RC_SETUID;
    //if(seteuid(uid)) return RC_SETUID;
    //if(setegid(gid)) return RC_SETUID;

    /* Stat the target script. */
    char uid_ok = 1;
    struct stat stat_buf;
    if(stat(target, &stat_buf)) return RC_STAT;
    int modes = stat_buf.st_mode;

    /* Never allow world-write. */
    if(modes & S_IWOTH) return RC_WORLD_WRITE;

	/*
    // Only allow user miss-match if check_gid is set. 
    if(uid != stat_buf.st_uid)
    {
      if(!check_gid) return RC_WRONG_USER;
      uid_ok = 0;
    }

    // If group doesn't match, don't allow group-write.
     //  Also, don't allow if neither user or group match.
    if(gid != stat_buf.st_gid)
    {
      if(modes & S_IWGRP) return RC_GROUP_WRITE;
      if(!uid_ok) return RC_WRONG_GROUP;
    }
	*/

    /* All checks passed, let's become the target! */
    execv(target, argv);
    return RC_EXEC;

  }

  /* Here we're in the parent. Wait for the child to be done, and return. */
  int status;
  wait(&status);
  return status;

}

