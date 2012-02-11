// Enable debugging. With Net_Sieve 1.2.0 or later, the sieve protocol
// communication is logged with the DEBUG level. Earlier versions print the
// log to the screen.
$backends['sieve']['params']['debug'] = false;

// If using Dovecot or any other Sieve implementation that requires folder
// names to be UTF-8 encoded, set this parameter to true.
$backends['sieve']['scriptparams']['utf8'] = false;
