import http.client, urllib.parse

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

# Login request
conn = http.client.HTTPConnection("localhost", 80)
cookie_hdr = "; ".join([f"{k}={v}" for k, v in cookies.items()])
xsrf = urllib.parse.unquote(cookies.get('XSRF-TOKEN', ''))

conn.request("POST", "/api/v1/auth/login",
    body='{"email":"ceo@example.com","password":"password123"}',
    headers={
        "Content-Type": "application/json",
        "Accept": "application/json",
        "Origin": "http://localhost:5173",
        "X-XSRF-TOKEN": xsrf,
        "Cookie": cookie_hdr,
    })

response = conn.getresponse()

# Count CORS headers
cors_headers = {}
for h, v in response.getheaders():
    h_lower = h.lower()
    if 'access-control' in h_lower or 'origin' in h_lower:
        if h not in cors_headers:
            cors_headers[h] = []
        cors_headers[h].append(v)

print(f"Status: {response.status}\n")
print("CORS Headers:")
for header, values in cors_headers.items():
    print(f"  {header}: {len(values)} occurrence(s)")
    for i, val in enumerate(values, 1):
        print(f"    [{i}] {val}")

print(f"\nDuplicate headers? {any(len(v) > 1 for v in cors_headers.values())}")

if not any(len(v) > 1 for v in cors_headers.values()):
    print("\n✓ SUCCESS: No duplicate CORS headers!")
else:
    print("\n✗ FAIL: Duplicate headers still present")

response.read()
conn.close()
