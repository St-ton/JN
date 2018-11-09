1. Docker installieren
2. Dockerfile anlegen
3. Bei Gitlab Container Registry anmelden: "sudo docker login gitlab.jtl-software.de:4567"
4. Dockerfile builden "sudo docker build --no-cache -t gitlab.jtl-software.de:4567/jtlshop/shop4/building_testing-phpX.X ."
5. Docker Image hoch laden: "sudo docker push gitlab.jtl-software.de:4567/jtlshop/shop4/building_testing-phpX.X"
6. Docker image in der gitlab.yml referenzieren.
