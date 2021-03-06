#!/bin/bash
set -e

BASEDIR="$(dirname $0)"
DIR="${BASEDIR}/../.."
NS=${NS:-"zippy"}
CONTEXT=${CONTEXT:-"minikube"}
RELEASE_NAME="zippy"
CHART_DIR="${DIR}/infra/helm/zippy"
VALUE_SRC="${DIR}/bin/dev/myvalues.yaml"

kubectl config use-context ${CONTEXT}

case $1 in
  uninstall)
    helm uninstall ${RELEASE_NAME} || true;
    ;;
  validate)
    helm install --dry-run --debug ${RELEASE_NAME} "${CHART_DIR}" \
        -f "${VALUE_SRC}" \
        --namespace $NS
    ;;
  update)
    echo "Updating..."
    helm upgrade ${RELEASE_NAME} "${CHART_DIR}" \
        -f "${VALUE_SRC}" \
        --namespace $NS
    ;;
  *)
    if [ ! -d "${CHART_DIR}/charts" ]; then
      (cd "${CHART_DIR}" && helm dependency update)
    fi
    kubectl create ns $NS || true
    helm uninstall ${RELEASE_NAME} --namespace $NS || true;
    kubectl -n $NS delete jobs.batch zippy-rabbitmq-vhost-setup || true;
    while [ $(kubectl -n $NS get pvc | wc -l) -gt 0 ] || [ $(kubectl -n $NS get pods | wc -l) -gt 0 ]
    do
      echo "Waiting for resources to be deleted..."
      sleep 5
    done
    echo "Installing release ${RELEASE_NAME} in namespace $NS..."
    helm install ${RELEASE_NAME} "${CHART_DIR}" \
        -f "${VALUE_SRC}" \
        --namespace $NS
    ;;
esac
