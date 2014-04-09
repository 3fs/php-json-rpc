#!/bin/bash

set -u

####################################################################################################
### functions

_usage()
{
    echo "QA tools"
    echo ""
    echo "Usage: build/qa.sh command"
    echo ""
    echo "Available commands:"
    echo "  all     run all"
    echo "  lint    PHP syntax check (lint)"
    echo "  unit    unit tests & code coverage report (phpunit)"
    echo "  cs      PHP coding standards (phpcs)"
    echo "  md      PHP mess detector (phpmd)"
    echo "  cpd     PHP copy/paste detector (phpcpd)"
    echo "  help    Display this help message"
    echo ""
    exit
}


####################################################################################################
### main


case "${1:-}" in
    # run them all!
    all)
        cmd="./build/qa.sh lint; ./build/qa.sh unit; ./build/qa.sh cs; ./build/qa.sh md; ./build/qa.sh cpd"
        ;;
    # PHP code lint
    lint)
        cmd="find src tests/unit -type f -name '*.php' | xargs -n1 -P4 php -l";
        ;;
    # PHP unit tests with code coverage
    unit)
        cmd="./vendor/bin/phpunit -c phpunit.xml";
        ;;
    # PHP coding standards
    cs)
        cmd="./vendor/bin/phpcs --standard=PSR2 src tests/unit"
        ;;
    # PHP mess detector
    md)
        cmd="./vendor/bin/phpmd src,tests/unit text codesize,unusedcode,naming,controversial --strict";
        ;;
    # PHP copy/paste detector
    cpd)
        cmd="./vendor/bin/phpcpd src tests/unit";
        ;;
    # everything else :)
    *)
        _usage
        ;;
esac

# Run command through vagrant & ssh or directly?
VAGRANT_BIN=`which vagrant`;

# no vagrant found implies the command is run from vagrant directly
if [ -z "${VAGRANT_BIN}" ]
then
    eval "${cmd}"
else
    ${VAGRANT_BIN} ssh -c "cd /vagrant && ${cmd}"
fi
