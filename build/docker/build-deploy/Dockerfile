FROM mariadb:10.2
ENV MYSQL_ROOT_PASSWORD root
ENV MYSQL_DATABASE build_database
RUN mkdir /creation
RUN mkdir /archive
WORKDIR '/creation'
RUN echo "[GLOBAL]" > /root/.my.cnf
RUN echo "max_allowed_packet=64M" >> /root/.my.cnf
RUN apt-get update \
    && apt-get install -y apt-transport-https curl git gnupg iputils-ping libxml2-dev lsb-release patchutils software-properties-common wget unzip zip zlib1g-dev \
    && add-apt-repository ppa:ondrej/php \
    && apt-get update \
    && apt-get install -y php7.2 php7.2-bcmath php7.2-bz2 php7.2-cli php7.2-common php7.2-curl php7.2-dom php7.2-gd \
        php7.2-iconv php7.2-intl php7.2-json php7.2-mbstring php7.2-mysql php7.2-pdo php7.2-readline php7.2-simplexml \
        php7.2-soap php7.2-sqlite3 php7.2-xml php7.2-xsl php7.2-zip
RUN curl -sL https://deb.nodesource.com/setup_11.x | bash -
RUN apt-get install -y nodejs
RUN cd /root \
    && wget https://composer.github.io/installer.sig -O - -q | tr -d '\n' > installer.sig \
    && php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" \
    && php -r "if (hash_file('SHA384', 'composer-setup.php') === file_get_contents('installer.sig')) { echo 'Installer verified'; } else { echo 'Installer corrupt'; unlink('composer-setup.php'); } echo PHP_EOL;" \
    && php composer-setup.php \
    && php -r "unlink('composer-setup.php'); unlink('installer.sig');"
RUN chmod a+x /root/composer.phar && ln -s /root/composer.phar /usr/local/bin/composer