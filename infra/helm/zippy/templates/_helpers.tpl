{{- define "zippy.fullname" -}}
{{- if .Values.fullnameOverride -}}
{{- .Values.fullnameOverride | trunc 63 | trimSuffix "-" -}}
{{- else -}}
{{- $name := default "zippy" .Values.nameOverride -}}
{{- if contains $name .Release.Name -}}
{{- .Release.Name | trunc 63 | trimSuffix "-" -}}
{{- else -}}
{{- printf "%s-%s" .Release.Name $name | trunc 63 | trimSuffix "-" -}}
{{- end -}}
{{- end -}}
{{- end -}}

{{- define "zippy.rabbitmqCredentials" -}}
{{- with .Values.rabbitmq }}
{{- if .externalSecretName }}
{{- if .externalSecretMap.username }}
- name: RABBITMQ_USER
  valueFrom:
    secretKeyRef:
      name: {{ .externalSecretName }}
      key: {{ .externalSecretMap.username }}
{{- end }}
{{- if .externalSecretMap.password }}
- name: RABBITMQ_PASSWORD
  valueFrom:
    secretKeyRef:
      name: {{ .externalSecretName }}
      key: {{ .externalSecretMap.password }}
{{- end }}
{{- end }}
{{- end }}
{{- end -}}
