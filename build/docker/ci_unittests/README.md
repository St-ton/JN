# Gitlab Container Registry

Build and tag your image (with a specific file):
```
$> docker build -f Dockerfile.[xx] -t registry.gitlab.com/jtl-software/jtl-shop/core/ci-tests:php72 .
```

Login into the gitlab container-registry:
```
$> docker login registry.gitlab.com
```

and push:
```
$> docker push registry.gitlab.com/jtl-software/jtl-shop/core/ci-tests:php72
```
