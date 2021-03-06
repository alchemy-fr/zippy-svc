![Zippy SVC](https://zippy.readthedocs.io/en/latest/_static/img/project.png)

# [WIP] Zippy HTTP Service

A zip service to create archives from remote files and serve them.

## TODO

- [X] Add security
- [ ] Support multiple clients
- [ ] Support PATCH method (diff files in archive)
- [ ] Move archives to another storage (S3, FTP, any flysystem adapter)
- [ ] Add more download adapters (FTP, S3, ...)
- [ ] Support compression options

## Security

In order to authenticate client, you must send the following authorization header:
```
Authorization: <client-id>:<client-secret>
```

## Usage

```bash
docker-compose up -d
```

Test your first archive:
```bash
curl --location --request POST 'http://localhost:3088/archives' \
--header 'Content-Type: application/json' \
--header 'Authorization: client:secret' \
--data-raw '{
    "identifier": "my_first_archive",
    "files": [
        {
            "uri": "https://img-19.ccm2.net/8vUCl8TXZfwTt7zAOkBkuDRHiT8=/1240x/smart/b829396acc244fd484c5ddcdcb2b08f3/ccmcms-commentcamarche/20494859.jpg",
            "path": "1.jpg"
        },
        {
            "uri": "https://d1fmx1rbmqrxrr.cloudfront.net/cnet/optim/i/edit/2019/04/eso1644bsmall__w770.jpg",
            "path": "2.jpg"
        }
    ]
}'
```

### Parameters

- **MAX_EXPIRATION_TIME**
Set the maximum retention allowed for an archive. 
Default: `-1` no maximum

## Helm & Kubernetes

See [Helm doc](./infra/helm)

> Note that `identifier` is optional. If not provided, a generated one will be returned based on the files.

## Developers

Configure docker-compose to use the dev file, then install PHP dependencies:

```bash
export COMPOSE_FILE=docker-compose.yaml:docker-compose.dev.yaml
docker-compose run --rm fpm composer install
```
