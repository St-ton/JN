FROM php:7.2-cli
WORKDIR '/build'
RUN apt-get update && apt-get install apt-transport-https curl git gnupg iputils-ping lsb-release patchutils software-properties-common wget unzip zip -y
RUN curl -sL https://deb.nodesource.com/setup_11.x | bash -
RUN apt-get install -y nodejs
RUN cd /root \
    && wget https://composer.github.io/installer.sig -O - -q | tr -d '\n' > installer.sig \
    && php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" \
    && php -r "if (hash_file('SHA384', 'composer-setup.php') === file_get_contents('installer.sig')) { echo 'Installer verified'; } else { echo 'Installer corrupt'; unlink('composer-setup.php'); } echo PHP_EOL;" \
    && php composer-setup.php \
    && php -r "unlink('composer-setup.php'); unlink('installer.sig');"
RUN chmod a+x /root/composer.phar && ln -s /root/composer.phar /usr/local/bin/composer