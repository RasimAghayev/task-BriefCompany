#!/bin/sh
# The entry point of the application container.
#
# The key of the application is read from the environment. When the
# environment does not carry one, a random key is generated for this
# container, so the application starts either way. The key is not written
# anywhere, so it changes with every start of the container.
set -e

if [ -z "${APP_KEY:-}" ]; then
    APP_KEY="base64:$(head -c 32 /dev/urandom | base64)"
    export APP_KEY
    echo "app: no APP_KEY was given, a random one was generated for this container"
fi

exec "$@"
