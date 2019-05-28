1. Docker installieren
2. Dockerfile anlegen
3. Bei Gitlab Container Registry anmelden: "sudo docker login registry.gitlab.com"
4. Dockerfile builden "sudo docker build --no-cache -f build/docker/DIR/Dockerfile -t registry.gitlab.com/jtl-software/jtl-shop/core/IMAGENAME ."
5. Docker Image hochladen: "sudo docker push registry.gitlab.com/jtl-software/jtl-shop/core/IMAGENAME"
6. Docker image in der gitlab.yml referenzieren.