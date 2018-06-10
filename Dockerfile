FROM centos:centos7
MAINTAINER DXkite dxkite(at)gmail

RUN yum install -y net-tools 

# get xampp
RUN curl -o xampp-linux-installer.run "https://downloadsapachefriends.global.ssl.fastly.net/xampp-files/7.2.5/xampp-linux-x64-7.2.5-0-installer.run?from_af=true"

# install xampp
RUN chmod +x xampp-linux-installer.run
RUN bash -c './xampp-linux-installer.run'
RUN ln -sf /opt/lampp/lampp /usr/bin/lampp


COPY ./ /suda
RUN cp /suda/script/docker_start.sh /start.sh
RUN mkdir /public
RUN cp -R /suda/system/resource/project/public /public
RUN chmod a+rw /public
RUN sed -i 's/\/opt\/lampp\/htdocs/\/public/g' /opt/lampp/etc/httpd.conf

VOLUME ["/app"]
EXPOSE 80
