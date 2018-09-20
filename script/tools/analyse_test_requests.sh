#!/bin/bash
# LogFormat "%V %h %D %l %u %t \"%r\" %s %b" vhost_common

LOG_FILE=$1

function analyse()
{
  max=`cat ${LOG_FILE}  | grep "$1" | awk '{print $3}' | sort -n | tail -n 1`
  min=`cat ${LOG_FILE}  | grep "$1" | awk '{print $3}' | sort -n | head -n 1`
  avg=`cat ${LOG_FILE}  | grep "$1" | awk '{a += $3 } END { printf("%f",a/NR) }'`
  total=`cat ${LOG_FILE}  | grep "$1" | awk 'END { print NR }'`
  echo "analyzed $1 in ${total} requests : max ${max}, min  ${min}, avg ${avg}" 
}

WEBSITES=('suda.atd3.org' 'echo.atd3.org' 'think.atd3.org' 'laravel.atd3.org')

for NAME in ${WEBSITES[@]}
do
  analyse "${NAME}"
done

 
