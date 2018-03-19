FROM php:7.2
RUN apt-get update
RUN apt-get install -y wget git unzip
RUN cd /root \
  && wget https://composer.github.io/installer.sig -O - -q | tr -d '\n' > installer.sig \
  && php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" \
  && php -r "if (hash_file('SHA384', 'composer-setup.php') === file_get_contents('installer.sig')) { echo 'Installer verified'; } else { echo 'Installer corrupt'; unlink('composer-setup.php'); } echo PHP_EOL;" \
  && php composer-setup.php \
  && php -r "unlink('composer-setup.php'); unlink('installer.sig');"
