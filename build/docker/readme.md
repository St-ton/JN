1. Docker installieren
2. Dockerfile anlegen
3. Bei Gitlab Container Registry anmelden: "sudo docker login gitlab.jtl-software.de:4567"
4. Dockerfile builden "sudo docker build --no-cache -f build/docker/DIR/Dockerfile -t gitlab.jtl-software.de:4567/jtlshop/shop4/IMAGENAME ."
5. Docker Image hochladen: "sudo docker push gitlab.jtl-software.de:4567/jtlshop/shop4/IMAGENAME"
6. Docker image in der gitlab.yml referenzieren.