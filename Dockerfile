FROM ubuntu:16.04

MAINTAINER skee <skee@token.ro>

EXPOSE 34000-36000

RUN dpkg --add-architecture i386
RUN apt-get update && apt-get install libstdc++5:i386 curl supervisor php7.0-cli nginx-core iproute2 -y

RUN mkdir /app
WORKDIR /app

RUN curl -O http://download.sopcast.com/download/sp-auth.tgz
RUN tar -xf sp-auth.tgz
RUN curl -O http://streams.magazinmixt.ro/ro/streams.json

COPY station_parser.php /
COPY docker-entrypoint.sh /

ENTRYPOINT ["/docker-entrypoint.sh"]
