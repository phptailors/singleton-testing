#!/usr/bin/env bash

here="$(dirname $0)";
top="$(dirname $here)";


failed=0

info() {
  echo -e "\033[0;92minfo: $@\033[0m"
}

warning() {
  echo -e "\033[0;33mwarning: $@\033[0m" >&2
}

error() {
  echo -e "\033[0;31merror: $@\033[0m" >&2
}

execute() {
  echo -e "\033[0;92m$@\033[0m";
  "$@"
}

check_result() {
    if [[ $1 -ne 0 ]]; then
        error "check failed with status code: $1"
        failed=$((failed+1));
    else
        info "check succeeded"
    fi
}

php=php
php_version=$($php -r 'printf("%s.%s", PHP_MAJOR_VERSION, PHP_MINOR_VERSION);');
psalm_baseline=".psalm/baseline-php${php_version}.xml"

pushd $top > /dev/null 2>&1
    info "running phpunit"
    execute $php vendor-bin/phpunit/vendor/bin/phpunit
    check_result $?;
    echo "";

    echo "";
    info "running vimeo/psalm"
    if [[ -f  $psalm_baseline ]]; then
        execute $php vendor-bin/psalm/vendor/bin/psalm --no-progress --no-cache --show-info=true --stats --php-version="$php_version" --use-baseline="$psalm_baseline"
    else
        execute $php vendor-bin/psalm/vendor/bin/psalm --no-progress --no-cache --show-info=true --stats --php-version="$php_version"
    fi
    check_result $?;
    echo "";

    echo "";
    info "runnig php-cs-fixer"
    execute $php vendor-bin/php-cs-fixer/vendor/bin/php-cs-fixer fix --diff --dry-run --show-progress=dots --using-cache=no --verbose
    check_result $?;
    echo "";

    echo "";
    info "runnig composer-require-checker"
    execute $php vendor-bin/composer-require-checker/vendor/bin/composer-require-checker check
    check_result $?;
    echo "";

    ## echo "";
    ## info "runnig roave-backward-compatibility-check"
    ## execute $php vendor-bin/backward-compatibility-check/vendor/bin/roave-backward-compatibility-check
    ## check_result $?
    ### echo "";
popd >/dev/null 2>&1

if [[ $failed -eq 1 ]]; then
    error "!!!!!!!!! 1 check failed !!!!!"
elif [[ $failed -gt 1 ]]; then
    error "!!!!!!!!! $failed checks failed !!!!!"
else
    info "all checks succeeded"
fi
