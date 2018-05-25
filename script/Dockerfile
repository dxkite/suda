FROM centos:centos7

MAINTAINER DXkite dxkite(at)gmail

# install xampp
RUN yum install -y net-tools 
RUN curl -o xampp-linux-installer.run "https://downloadsapachefriends.global.ssl.fastly.net/xampp-files/7.2.5/xampp-linux-x64-7.2.5-0-installer.run?from_af=true"
RUN chmod +x xampp-linux-installer.run
RUN bash -c './xampp-linux-installer.run'
RUN ln -sf /opt/lampp/lampp /usr/bin/lampp

# download framework
RUN yum install -y git 
RUN git clone https://github.com/DXkite/suda

# config app
COPY ./ /app
RUN sed -i 's/\/opt\/lampp\/htdocs/\/public/g' /opt/lampp/etc/httpd.conf
RUN mkdir public
RUN chmod a+rw /app
RUN chmod a+rw /public
RUN chmod a+rw /app/data
RUN cp /suda/script/docker_start.sh /start.sh
RUN cp /suda/system/resource/project/public/* /public

VOLUME ["/app"]

EXPOSE 80


CMD ["sh","-c","sh start.sh && tail -f /opt/lampp/logs/access_log" ]
