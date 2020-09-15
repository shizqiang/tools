#!/bin/python
# -*- coding: utf-8 -*-  

import urllib
import urllib2

url = 'http://127.0.0.1/test.php?ss=1'

# params = {'name': 'shizq', 'pass': '123456'}
# params = urllib.urlencode(params)
data  = '--123\r\n'
data += 'Content-Disposition: form-data; name=\"title\"\r\n'
data += '\r\ntitle_value\r\n'
data += '--123\r\n'
data += 'Content-Disposition: form-data; name=\"cover_img\"; filename=\"avatar.jpg\"\r\n'
data += 'Content-Type: image/jpeg\r\n'
fp = open('avatar.jpg', 'rb')
buf = fp.read(2048)
data += '\r\n';
while buf:
    data += buf
    buf = fp.read(2048)
data += '\r\n'
fp.close()
data += '--123\r\n'

req = urllib2.Request(url = url, data = data)
req.add_header('Content-Type', 'multipart/form-data; boundary=123')
resp = urllib2.urlopen(req)
content = resp.read()
print(content)

# 这样就能成功上传文件了！^_^