FROM mariadb:10.2
ENV MYSQL_ROOT_PASSWORD root
ENV MYSQL_DATABASE build_database
WORKDIR '/build'
RUN mkdir /archive
RUN echo "[GLOBAL]" > /root/.my.cnf
RUN echo "max_allowed_packet=64M" >> /root/.my.cnf
RUN apt-get update \
    && apt-get install -y git patchutils software-properties-common wget unzip zip \
    && add-apt-repository ppa:ondrej/php \
    && apt-get update \
    && apt-get install -y php7.1 php7.1-bcmath php7.1-bz2 php7.1-cli php7.1-common php7.1-curl php7.1-intl php7.1-json \
        php7.1-mbstring php7.1-mysql php7.1-readline php7.1-soap php7.1-sqlite3 php7.1-xml php7.1-xsl php7.1-zip
RUN cd /root \
    && wget https://composer.github.io/installer.sig -O - -q | tr -d '\n' > installer.sig \
    && php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" \
    && php -r "if (hash_file('SHA384', 'composer-setup.php') === file_get_contents('installer.sig')) { echo 'Installer verified'; } else { echo 'Installer corrupt'; unlink('composer-setup.php'); } echo PHP_EOL;" \
    && php composer-setup.php \
    && php -r "unlink('composer-setup.php'); unlink('installer.sig');"
RUN chmod a+x /root/composer.phar && ln -s /root/composer.phar /usr/local/bin/composer