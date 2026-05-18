import requests

url = "https://serverless.roboflow.com/workflow/<YOUR_WORKFLOW_ID>?api_key=oLT*****************"

files = {
    "image": open("YOUR_IMAGE.jpg", "rb")
}

data = {
    "workspace_name": "alejandro-castro",
    "parameters": '{"classes":"Good, Defected"}'
}

response = requests.post(url, files=files, data=data)
print(response.json())
