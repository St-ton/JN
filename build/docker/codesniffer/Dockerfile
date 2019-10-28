FROM alpine:latest
WORKDIR /tmp
RUN apk --no-cache add \
    bash \
    ca-certificates \
    curl \
    libxml2-dev \
    php-simplexml \
    php7-tokenizer \
    php7-cli \
    php7-dom \
    php7-iconv \
    php7-phar \
    php7-xdebug \
    php7-xml \
    php7-xmlwriter \
    php7-zlib
RUN curl -OL https://squizlabs.github.io/PHP_CodeSniffer/phpcs.phar && \
    cp /tmp/phpcs.phar /usr/local/bin/phpcs && \
    chmod +x /usr/local/bin/phpcs
# Set some useful defaults to phpcs
# show_progress - I like to see a progress while phpcs does its magic
# colors - Enable colors; My terminal supports more than black and white
# report_width - I am using a large display so I can afford a larger width
# encoding - Unicode all the way
RUN /usr/local/bin/phpcs --config-set show_progress 1 && \
    /usr/local/bin/phpcs --config-set colors 1 && \
    /usr/local/bin/phpcs --config-set report_width 140 && \
    /usr/local/bin/phpcs --config-set encoding utf-8