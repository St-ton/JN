1. Docker installieren
2. Dockerfile anlegen
3. Bei Gitlab Container Registry anmelden: "sudo docker login gitlab.jtl-software.de:4567"
4. Dockerfile builden "sudo docker build -t gitlab.jtl-software.de:4567/jtlshop/shop4/xyz ."
5. Docker Image hoch laden: "sudo docker push gitlab.jtl-software.de:4567/jtlshop/shop4/xyz"
6. Docker image in der gitlab.yml referenzieren.
