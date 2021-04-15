![Zippy SVC](https://zippy.readthedocs.io/en/latest/_static/img/project.png)

# [WIP] Zippy HTTP Service

A zip service to create archives from remote files.

## TODO

- [ ] Add security (JWT with shared public key)
- [ ] Move archives to another storage (S3, FTP, any flysystem adapter)
- [ ] Add more download adapters (FTP, S3, ...)
- [ ] Support compression options

## Security

Security is not implement yet. You should consider using this service on a very private network, as an internal service.

## Usage

```bash
docker-compose up -d
```

Test your first archive:
```bash
curl --location --request POST 'http://localhost:3088/archives' \
--header 'Content-Type: application/json' \
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
