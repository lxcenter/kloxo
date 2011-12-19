if [ "$1" == "--help" ] ; then
	echo
	echo " ------------------------------------------------------------------------------"
	echo "  format: sh $0 [--server=] [--client=]"
	echo " ------------------------------------------------------------------------------"
	echo "  --server="
	echo "      localhost: master (default)"
	echo "      slaveid: slave id (ip or hostname)"
	echo "      all: all servers (master and slave)"
	echo "      - optional and 'localhost' for default value"
	echo "      - multiple servers separated by ',' (comma)"
	echo "  --client="
	echo "      - optional and 'all' for default value"
	echo "      - multiple clients separated by ',' (comma)"
	echo "      - use this option automatically set '--server=all'"
	echo
	exit;
fi

echo
echo "- For help, type '$0 --help'"

if [ "$#" == 0 ] ; then
	echo "- No argument supplied. Defaulting to localhost (master) servers"
fi

echo