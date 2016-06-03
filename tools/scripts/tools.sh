#!/usr/bin/env bash

fgRed=$(tput setaf 1)     ; fgGreen=$(tput setaf 2)  ; fgBlue=$(tput setaf 4)
fgMagenta=$(tput setaf 5) ; fgYellow=$(tput setaf 3) ; fgCyan=$(tput setaf 6)
fgWhite=$(tput setaf 7)   ; fgBlack=$(tput setaf 0)

bgRed=$(tput setab 1)     ; bgGreen=$(tput setab 2)  ; bgBlue=$(tput setab 4)
bgMagenta=$(tput setab 5) ; bgYellow=$(tput setab 3) ; bgCyan=$(tput setab 6)
bgWhite=$(tput setab 7)   ; bgBlack=$(tput setab 0)

B=$(tput bold) ; U=$(tput smul) ; C=$(tput sgr0)

# $1 text
msg()
{
    printf "${fgYellow}Ξ${C} $1\n"
}

# $1 text
error()
{
    printf "${fgRed}Ξ${C} $1\n"
    exit -1
}

# $1 text
success()
{
    printf "${fgGreen}Ξ${C} $1\n"
    exit -1
}

# $1 path to append
pathadd()
{
    if [ -d "$1" ] && ! echo $PATH | grep -E -q "(^|:)$1($|:)" ; then
        PATH="$PATH:${1%/}"
    fi
}

# $1 path to remove
pathrm()
{
    PATH="$(echo $PATH | sed -e "s;\(^\|:\)${1%/}\(:\|\$\);\1\2;g" -e 's;^:\|:$;;g' -e 's;::;:;g')"
}