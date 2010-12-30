<?php

$mode = 1;

function irc_read($socket, $len) {
	global $mode;

	$res = fread($socket, $len);
	$res = rtrim($res);
	return $res;
}

function irc_write($socket, $msg) {
    global $mode;
	return fputs($socket, $msg);
}

function irc_nb($socket) {
    global $mode;
	stream_set_blocking($socket, false);
}

function irc_open($serv_addr, $serv_port, &$errno, &$errstr) {
	global $mode;
	$fd =  stream_socket_client("ssl://$serv_addr:$serv_port");
	return $fd;
}

function irc_close($socket) {
    global $mode;
	return @fclose($socket);
}

function flush_server_buffer() {
    // flush();
    @ob_flush();
}

