1. Docker installieren
2. Dockerfile anlegen
3. Dockerfile builden "sudo docker build -t gitlab.jtl-software.de:4567/jtlshop/shop4/xyz ."
4. Bei Gitlab Container Registry anmelden: "docker login gitlab.jtl-software.de:4567"
5. Docker Image hoch laden: "docker push gitlab.jtl-software.de:4567/jtlshop/shop4/xyz"
6. Docker image in der gitlab.yml referenzieren.
