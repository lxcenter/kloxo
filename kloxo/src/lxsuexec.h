/*

User configuration for ExecWrap. Please review ALL items in this file, before you compile.
Remember, the security of your system depends on getting these right!

See the README for documentation.

*/


/* Our parent must have this UID, or we will abort. */
#define PARENT_UID              48

/* Minimum UID we can switch to. */
#define TARGET_MIN_UID          500

/* Minimum GID we can switch to. */
#define TARGET_MIN_GID          100

/* Path prefix all targets must start with. */
#define TARGET_PATH_PREFIX      "/"

/* Default UID to switch to, if none given. */
#define DEFAULT_UID             65534

/* Default GID to switch to, if none given. */
#define DEFAULT_GID             65534

