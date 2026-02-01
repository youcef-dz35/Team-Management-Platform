#!/bin/bash
# wait-for-it.sh - Wait for a service to be available
# Usage: ./wait-for-it.sh host:port [-t timeout] [-- command args]

set -e

TIMEOUT=30
QUIET=0
HOST=""
PORT=""
CHILD=0

echoerr() {
    if [ "$QUIET" -ne 1 ]; then
        echo "$@" 1>&2
    fi
}

usage() {
    cat << USAGE >&2
Usage:
    $0 host:port [-t timeout] [-- command args]
    -q | --quiet                        Don't output any status messages
    -t TIMEOUT | --timeout=timeout      Timeout in seconds (default: 30)
    -- COMMAND ARGS                     Execute command with args after the test finishes
USAGE
    exit 1
}

wait_for() {
    if [ "$TIMEOUT" -gt 0 ]; then
        echoerr "$0: waiting $TIMEOUT seconds for $HOST:$PORT"
    else
        echoerr "$0: waiting for $HOST:$PORT without a timeout"
    fi

    start_ts=$(date +%s)
    while :
    do
        if [ "$QUIET" -ne 1 ]; then
            nc -z "$HOST" "$PORT" > /dev/null 2>&1
        else
            nc -z "$HOST" "$PORT" > /dev/null 2>&1
        fi

        result=$?
        if [ $result -eq 0 ]; then
            end_ts=$(date +%s)
            echoerr "$0: $HOST:$PORT is available after $((end_ts - start_ts)) seconds"
            break
        fi

        sleep 1

        if [ "$TIMEOUT" -gt 0 ]; then
            current_ts=$(date +%s)
            if [ $((current_ts - start_ts)) -ge "$TIMEOUT" ]; then
                echoerr "$0: timeout occurred after waiting $TIMEOUT seconds for $HOST:$PORT"
                return 1
            fi
        fi
    done
    return 0
}

while [ $# -gt 0 ]
do
    case "$1" in
        *:* )
            HOST=$(echo "$1" | cut -d: -f1)
            PORT=$(echo "$1" | cut -d: -f2)
            shift 1
            ;;
        -q | --quiet)
            QUIET=1
            shift 1
            ;;
        -t)
            TIMEOUT="$2"
            if [ "$TIMEOUT" = "" ]; then break; fi
            shift 2
            ;;
        --timeout=*)
            TIMEOUT="${1#*=}"
            shift 1
            ;;
        --)
            shift
            CHILD=1
            break
            ;;
        --help)
            usage
            ;;
        *)
            echoerr "Unknown argument: $1"
            usage
            ;;
    esac
done

if [ "$HOST" = "" ] || [ "$PORT" = "" ]; then
    echoerr "Error: you need to provide a host and port to test."
    usage
fi

wait_for
RESULT=$?

if [ "$CHILD" -gt 0 ]; then
    if [ $RESULT -ne 0 ]; then
        exit $RESULT
    fi
    exec "$@"
else
    exit $RESULT
fi
