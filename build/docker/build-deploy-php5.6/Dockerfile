FROM mariadb:5.5
WORKDIR '/build'
RUN mkdir /archive
RUN echo "[GLOBAL]" > /root/.my.cnf
RUN echo "max_allowed_packet=64M" >> /root/.my.cnf
RUN apt-get update \
    && apt-get install -y language-pack-en-base \
    && export LC_ALL=en_US.UTF-8 \
    && export LANG=en_US.UTF-8 \
    && apt-get install -y apt-transport-https curl git gnupg iputils-ping lsb-release patchutils software-properties-common wget unzip zip \
    && add-apt-repository ppa:ondrej/php \
    && apt-get update \
    && apt-get install -y php5.6 php5.6-bcmath php5.6-bz2 php5.6-cli php5.6-common php5.6-curl php5.6-intl php5.6-json \
        php5.6-mbstring php5.6-mysql php5.6-readline php5.6-soap php5.6-sqlite3 php5.6-xml php5.6-xsl php5.6-zip
RUN curl -sL https://deb.nodesource.com/setup_10.x | bash -
RUN apt-get install -y nodejs
RUN cd /root \
    && wget https://composer.github.io/installer.sig -O - -q | tr -d '\n' > installer.sig \
    && php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" \
    && php -r "if (hash_file('SHA384', 'composer-setup.php') === file_get_contents('installer.sig')) { echo 'Installer verified'; } else { echo 'Installer corrupt'; unlink('composer-setup.php'); } echo PHP_EOL;" \
    && php composer-setup.php \
    && php -r "unlink('composer-setup.php'); unlink('installer.sig');"
RUN chmod a+x /root/composer.phar && ln -s /root/composer.phar /usr/local/bin/composer