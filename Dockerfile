ARG PHP_TAG

FROM wodby/drupal-php:${PHP_TAG}

USER root
COPY --chown=wodby:wodby . /var/www/html

# Configure timezone
RUN apk add --update tzdata \
  && cp /usr/share/zoneinfo/America/Los_Angeles /etc/localtime \
  && echo "America/Los_Angeles" >  /etc/timezone


RUN composer install
USER wodby

COPY init /docker-entrypoint-init.d/