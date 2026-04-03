# Developer Notes

## Setup local Development Environment

**Pre-requisite:**

- HumHub is accessible from Docker e.g. via `http://host.docker.internal` container (see `--add-host` below)
- In this example HumHub is running on `http://humhub.localhost` AND `http://host.docker.internal`
- Collabora - Module Configuration
  - WOPI Client Server: `http://localhost:9980`
  - WOPI Host: `http://host.docker.internal`

**Start Collabora Code Server:**

```bash
docker run -d \
  --name collabora \
  --cap-add=SYS_ADMIN \
  -e extra_params="--o:ssl.enable=false" \
  -e server_name="humhub.localhost:9980" \
  -p 9980:9980 \
  --add-host host.docker.internal:host-gateway \
  --network default \
  collabora/code
```
