import urllib.parse, http.client, json

# Get CSRF cookie
conn = http.client.HTTPConnection("localhost", 80)
conn.request("GET", "/sanctum/csrf-cookie", headers={"Origin": "http://localhost:5173"})
response = conn.getresponse()

cookies = {}
for h, v in response.getheaders():
    if h.lower() == 'set-cookie':
        name, val = v.split(';')[0].split('=', 1)
        cookies[name] = val

response.read()
conn.close()

# Login
conn = http.client.HTTPConnection("localhost", 80)
cookie_hdr = "; ".join([f"{k}={v}" for k, v in cookies.items()])
xsrf = urllib.parse.unquote(cookies.get('XSRF-TOKEN', ''))

conn.request("POST", "/api/v1/auth/login",
    body=json.dumps({"email": "ceo@example.com", "password": "password123"}),
    headers={
        "Content-Type": "application/json",
        "Origin": "http://localhost:5173",
        "X-XSRF-TOKEN": xsrf,
        "Cookie": cookie_hdr,
    })

response = conn.getresponse()
status = response.status
body = response.read().decode()
conn.close()

print(f"Status: {status}")
if status == 200:
    print("SUCCESS - Login works!")
    print(f"Response length: {len(body)} bytes")
elif status == 419:
    print("FAIL - 419 CSRF mismatch")
else:
    print(f"FAIL - Unexpected: {status}")
print(f"First 200 chars: {body[:200]}")
