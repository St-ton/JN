# vue-install

> A JTL Shop installer

## Prepare the installer

``` bash
$> cd /var/www/html/shoproot/build/components/vue-installer/
$> composer install
```
The composer run will initiate a `npm` dependency update and will execute all necessary steps needed for the preparation of the installation process.

## Run the installation

After the preparation of the installer open your browser and surf to the `/install`-folder in your shop-root:
```
http://host/shoproot/install/
```
Now follow the instructions of the installer.

### Build Setup by hand (without composer-run)

``` bash
# install dependencies
$> npm install

# serve with hot reload at localhost:8080
$> npm run dev

# build for production with minification
$> npm run build

# build for production and view the bundle analyzer report
$> npm run build --report
```
For detailed explanation on how "vue-things" work, checkout the [guide](http://vuejs-templates.github.io/webpack/) and [docs for vue-loader](http://vuejs.github.io/vue-loader).
