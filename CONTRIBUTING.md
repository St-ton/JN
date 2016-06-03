# How to contribute

JTL-Shop is a commercial open source software. Read [LICENSE.md](LICENSE.md) for further information. 

Git Repository: git@gitlab.jtl-software.de:jtlshop/shop4.git

Contribute your changes by adding a new branch and creating a merge request in gitlab. 
External developers: fork shop project master in your namespace and create the merge request.  

Merging into master branch is only permitted to developers with master permission. 

## Getting Started

* Make sure your ssh key is stored in your gitlab account
* Clone the jtl-shop repository: ```git clone git@gitlab.jtl-software.de:jtlshop/shop4.git mydevshop```
* init + update submodules: 
  ```
  git submodule init
  git submodule update
  ```
* get vendor libs: 
  ```
  cd includes
  composer update
  ```
* install shop in your browser /install/index.php or get shopcli to perform install/update/migrations: https://gitlab.jtl-software.de/jtlshop/shopcli

## Coding Guidelines

We basically follow [http://www.php-fig.org/psr/psr-2/](PSR-2) with some extra rules, specified in /.php-cs. 

Grab and install php-cs-fixer to fix php-style in jtl-shop automatically: 

````
wget http://get.sensiolabs.org/php-cs-fixer.phar -O php-cs-fixer
sudo chmod a+x php-cs-fixer
sudo mv php-cs-fixer /usr/local/bin/php-cs-fixer
```

Fix all php Files but not the exluded ones: 
```
php-cs-fixer fix .
```

Fix 1 File: 
```
php-cs-fixer fix admin/includes/dashboard_inc.php --config-file .php_cs
```

## Commit Messages

Always provide a short summary of your Codechange in the first line. 
Long description is optional. If needed, place a new line between summary and long description.  

Summary (first line): 

Start your commit message with "Fix" or "Re" or "Unfix" followed by the issue referenced. 
Next, provide a short description about the change and use words like "Improve, Fix, Add, Remove, Shorten, Update" e.g. to keep a good readability. 

Good: 
```
git commit -m "Fix #1234 - Fix wrong comparison operator"
```
```
git commit -m "Re #1234 - Add required attribute to mandatory fields"
```
```
git commit -m "Unfix jtlshop/shop4#12345 - Roll back last changes because jtlshop/shop4#12346 already solves this issue"
```

Bad: 
```
git commit -m ""
```
```
git commit -m "sql"
```
```
git commit -m "Schönheitskorrektur"
```
```
git commit -m "wrong comparison operator used"
```