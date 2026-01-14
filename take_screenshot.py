from playwright.sync_api import sync_playwright, expect

def run(playwright):
    browser = playwright.chromium.launch()
    page = browser.new_page()

    # Log in with the new user
    page.goto('http://127.0.0.1:8000/es/login')
    page.get_by_label('Nombre de usuario').fill('testuser')
    page.get_by_label('Contraseña').fill('password')
    page.get_by_role('button', name='Iniciar sesión').click()
    page.wait_for_url("http://127.0.0.1:8000/es")

    # Navigate to the hospitalized index page
    page.goto('http://127.0.0.1:8000/es/hospitalized')

    # Wait for the heading to be visible
    expect(page.get_by_role("heading", name="Índice de hospitalizados")).to_be_visible()

    page.screenshot(path='final_screenshot.png')
    browser.close()

with sync_playwright() as playwright:
    run(playwright)
