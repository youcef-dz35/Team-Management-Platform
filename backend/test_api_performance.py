import requests
import time

# Test token from previous login
token = "6|2gq7fLRTQANRRyrRk6yylQTpHAvNsduPUOk3Y8Gw6cbe3b16"

headers = {
    "Authorization": f"Bearer {token}",
    "Accept": "application/json",
}

print("Testing API endpoint performance...")
print("=" * 60)

# Test /stats endpoint
print("\n1. Testing /api/v1/conflicts/stats")
start = time.time()
response = requests.get("http://localhost/api/v1/conflicts/stats", headers=headers)
duration = time.time() - start
print(f"   Status: {response.status_code}")
print(f"   Duration: {duration:.3f}s")
print(f"   Response headers:")
for header in ['X-Query-Count', 'X-Query-Time', 'X-Total-Time']:
    if header in response.headers:
        print(f"     {header}: {response.headers[header]}")

# Test /me endpoint
print("\n2. Testing /api/v1/auth/me")
start = time.time()
response = requests.get("http://localhost/api/v1/auth/me", headers=headers)
duration = time.time() - start
print(f"   Status: {response.status_code}")
print(f"   Duration: {duration:.3f}s")
print(f"   Response headers:")
for header in ['X-Query-Count', 'X-Query-Time', 'X-Total-Time']:
    if header in response.headers:
        print(f"     {header}: {response.headers[header]}")

# Test /conflicts endpoint  
print("\n3. Testing /api/v1/conflicts?page=1")
start = time.time()
response = requests.get("http://localhost/api/v1/conflicts?page=1", headers=headers)
duration = time.time() - start
print(f"   Status: {response.status_code}")
print(f"   Duration: {duration:.3f}s")
print(f"   Response headers:")
for header in ['X-Query-Count', 'X-Query-Time', 'X-Total-Time']:
    if header in response.headers:
        print(f"     {header}: {response.headers[header]}")

print("\n" + "=" * 60)
print("Check Laravel logs for [PERF] entries with detailed metrics")
