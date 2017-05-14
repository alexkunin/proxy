FROM alpine

RUN echo "@testing http://nl.alpinelinux.org/alpine/edge/testing" >> /etc/apk/repositories \
    && apk add --update --no-cache \
        dante-server@testing \
        php5 \
        bind-tools \
        socat \
        dumb-init \
    && rm -rf /var/cache/apk/*

ADD sockd.conf /etc/sockd.conf
ADD init.sh /init.sh
ADD . /app/

EXPOSE 80
EXPOSE 1080
EXPOSE 53

ENTRYPOINT ["/usr/bin/dumb-init", "--"]
CMD ["/app/init.sh"]
