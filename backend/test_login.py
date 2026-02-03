import requests
import time
from urllib.parse import unquote

BASE_URL = "http://localhost"
LOGIN_URL = f"{BASE_URL}/api/v1/auth/login"
CSRF_URL = f"{BASE_URL}/sanctum/csrf-cookie"
FRONTEND_ORIGIN = "http://localhost:5173"

def test_login_detailed():
    """Detailed login test with cookie/session debugging"""
    
    print("=" * 60)
    print("LOGIN PERFORMANCE TEST - Simulating Real User (Debug Mode)")
    print("=" * 60)
    
    session = requests.Session()
    session.headers.update({
        "User-Agent": "Mozilla/5.0 (Windows NT 10.0; Win64; x64) Chrome/120.0.0.0",
        "Accept": "application/json",
    })
    
    # Step 1: Get CSRF Cookie (simulating page load  from frontend origin)
    print("\n[1/4] Fetching CSRF token from frontend origin...")
    start_csrf = time.time()
    
    try:
        csrf_response = session.get(CSRF_URL, headers={
            "Referer": f"{FRONTEND_ORIGIN}/login",
            "Origin": FRONTEND_ORIGIN,
            "Accept": "application/json"
        })
        csrf_duration = time.time() - start_csrf
        
        print(f"   ✓ CSRF fetch: {csrf_duration:.3f}s (Status: {csrf_response.status_code})")
        print(f"   Response headers: {dict(csrf_response.headers)}")
        print(f"   Cookies received: {dict(session.cookies)}")
        
    except Exception as e:
        print(f"   ✗ CSRF fetch failed: {e}")
        return
    
    # Step 2: Extract CSRF token
    print("\n[2/4] Extracting CSRF token...")
    
    if 'XSRF-TOKEN' not in session.cookies:
        print("   ✗ ERROR: No XSRF-TOKEN cookie found!")
        print(f"   Available cookies: {list(session.cookies.keys())}")
        return
    
    xsrf_token = unquote(session.cookies['XSRF-TOKEN']) 
    print(f"   ✓ XSRF-TOKEN extracted: {xsrf_token[:50]}...")
    
    # Step 3: Check for Laravel session cookie
    print("\n[3/4] Checking for Laravel session cookie...")
    session_cookies = [key for key in session.cookies.keys() if 'session' in key.lower() or 'laravel' in key.lower()]
    if session_cookies:
        print(f"   ✓ Session cookies found: {session_cookies}")
        for cookie_name in session_cookies:
            print(f"      {cookie_name}: {session.cookies[cookie_name][:50]}...")
    else:
        print("   ⚠ No obvious session cookie found")
        print(f"   All cookies: {list(session.cookies.keys())}")
    
    # Step 4: Attempt login with all proper headers
    print("\n[4/4] Attempting login with proper Origin/Referer...")
    
    headers = {
        "Content-Type": "application/json",
        "Accept": "application/json",
        "X-XSRF-TOKEN": xsrf_token,
        "Origin": FRONTEND_ORIGIN,
        "Referer": f"{FRONTEND_ORIGIN}/login",
    }
    
    payload = {
        "email": "ceo@example.com",
        "password": "password"
    }
    
    start_login = time.time()
    
    try:
        login_response = session.post(LOGIN_URL, json=payload, headers=headers)
        login_duration = time.time() - start_login
        
        print(f"   ✓ Login request: {login_duration:.3f}s (Status: {login_response.status_code})")
        
        # Analyze response
        print("\n" + "=" * 60)
        print("RESULTS")
        print("=" * 60)
        
        if login_response.status_code == 200:
            print("✓ Login SUCCESSFUL")
            data = login_response.json()
            print(f"   User: {data.get('user', {}).get('name')}")
            print(f"   Email: {data.get('user', {}).get('email')}")
            print(f"   Roles: {data.get('user', {}).get('roles')}")
        elif login_response.status_code == 419:
            print("✗ ERROR 419: CSRF Token Mismatch")
            print(f"   Response: {login_response.text[:200]}")
            print("\n   TROUBLESHOOTING:")
            print("   - Checking if session cookie persisted across requests...")
            print(f"   - Current cookies: {list(session.cookies.keys())}")
        else:
            print(f"✗ ERROR {login_response.status_code}")
            print(f"   Response: {login_response.text}")
        
        # Performance assessment
        print("\n" + "=" * 60)
        print("PERFORMANCE ANALYSIS")
        print("=" * 60)
        
        total_time = csrf_duration + login_duration
        print(f"Total time: {total_time:.3f}s")
        print(f"  - CSRF:  {csrf_duration:.3f}s")
        print(f"  - Login: {login_duration:.3f}s")
        
        if login_duration < 0.5:
            print("\n✓ EXCELLENT: Login is blazing fast! (<0.5s)")
        elif login_duration < 1.0:
            print("\n✓ GOOD: Login is fast (<1s)")
        elif login_duration < 3.0:
            print("\n⚠ OK: Login is acceptable (<3s)")
        else:
            print("\n⚠ NEEDS IMPROVEMENT: Login should be under 3s")
        
    except Exception as e:
        print(f"   ✗ Login request failed: {e}")
        import traceback
        traceback.print_exc()

if __name__ == "__main__":
    test_login_detailed()
