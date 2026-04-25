# Employee Business Cards

Employee Business Cards is a production-ready WordPress plugin for creating public, shareable digital business cards for employees.

## Installation

1. Upload the `employee-business-cards` folder to `/wp-content/plugins/`.
2. Activate **Employee Business Cards** from the WordPress plugins screen.
3. Go to **Employee Cards** in WP Admin.

## Usage

### Create Employee Card

1. Open **Employee Cards → Add New**.
2. Fill in **Business Card Details** fields:
   - Full name, job title, department, company, contact details, social links, location, bio, profile photo, optional custom slug.
3. Publish the card.
4. Use the **Public Card URL** side box to open or share the card.

### Settings

Go to **Employee Cards → Settings** and configure:

- Default company name
- Default website URL
- Enable QR code (disabled by default)
- QR provider type:
  - **Local (server-side cached)**: plugin fetches and stores QR images in uploads and serves local URLs.
  - **External provider URL**: plugin uses a direct external image URL pattern.
- QR code provider URL template (used only for External mode and must include `{url}`)
- Primary color
- Button style (rounded/square)

## Shortcodes

### Single card

```text
[employee_business_card id="123"]
```

### Grid of cards

```text
[employee_business_cards]
```

Optional attributes:

```text
[employee_business_cards company="Acme" department="Sales" limit="12" columns="3"]
```

## vCard Download

Each public card includes a **Save Contact** button that downloads a `.vcf` file.

The endpoint format is:

```text
?employee_card_vcf=POST_ID
```

Only published `employee_card` posts are valid.

## Notes

- Uses secure sanitization, escaping, capability checks, and nonce verification.
- Rewrite rules are flushed only on plugin activation/deactivation.
- Public CSS/JS and admin assets are loaded only when needed.
