=== Secure MCP Connector for Claude, ChatGPT and other AI providers ===
Contributors: cyberlord92
Tags: governance, abilities, mcp, ai, oauth
Requires at least: 6.9
Tested up to: 7.0
Requires PHP: 7.4
Stable tag: 1.3.1
License: Expat
License URI: https://plugins.miniorange.com/mit-license

AI governance for WordPress: expose your Abilities API as a secure, OAuth-protected MCP server for AI clients like ChatGPT and Claude.

== Description ==

miniOrange Secure MCP Server helps WordPress administrators with AI governance and policy enforcement: understanding, and controlling, what AI assistants and MCP clients are allowed to do on their site.

The WordPress Abilities API (available in WordPress 6.9 and later) lets plugins and WordPress core expose discrete, machine-callable capabilities — for example: get site info, create a post, or generate a summary. This plugin turns those abilities into a remote **Model Context Protocol (MCP)** server so AI clients can discover and invoke them, protected by a self-hosted OAuth 2.1 authorization server.

**What this version does**

* **NHI Registry with role-based access control.** An admin screen for creating non-human identities (NHIs) — named, role-based ability policies. For each WordPress role you choose which abilities that role may use through the NHI, with live capability-conflict detection. This is the default landing screen when you open the plugin.
* **Member view.** Every logged-in user gets a "My AI Access" screen showing exactly which tools their role can use and how to connect an AI client; administrators can switch between the admin and member views.
* **Per-ability toggle.** Enable or disable individual abilities from being exposed as MCP tools directly from the Abilities screen.
* **Abilities viewer.** A read-only admin screen that lists every ability registered on your site, with its label, description, category, source namespace, and full input/output JSON schema.
* **Connection guide.** A "Connect to AI" tab with step-by-step instructions and your site's MCP URL for connecting clients such as ChatGPT and Claude.
* **Built-in content abilities.** Create Post and Update Post abilities (exposed as MCP tools) so connected clients can draft and edit posts, gated by the user's capabilities.
* **MCP server.** A single Streamable HTTP endpoint that exposes every registered ability as an MCP tool. Tool calls run through the Abilities API, so each ability's own permission check still applies.
* **Self-hosted dynamic OAuth.** WordPress acts as its own OAuth 2.1 authorization server with OAuth 2.0 Dynamic Client Registration (RFC 7591), Protected Resource Metadata (RFC 9728), Authorization Server Metadata (RFC 8414), and Authorization Code flow with PKCE. Clients such as ChatGPT and Claude can register themselves and connect with no manual credential setup.

Every MCP request runs as the WordPress user who authorized it, so what an AI client can do is bounded by that user's own capabilities.

== Installation ==

1. Upload the plugin files to the `/wp-content/plugins/miniorange-secure-mcp-server` directory, or install the plugin through the WordPress plugins screen directly.
2. Activate the plugin through the "Plugins" screen in WordPress.
3. Open the "Secure MCP Server" menu item (under Tools) to review the NHI Registry and abilities registered on your site.
4. Connect an MCP client (see the FAQ) to `https://YOUR-SITE/wp-json/mosmcp/v1/mcp`.

== Frequently Asked Questions ==

= How do ChatGPT and Claude connect? =

Add a custom connector pointing at your MCP endpoint, `https://YOUR-SITE/wp-json/mosmcp/v1/mcp`. The client discovers the OAuth endpoints automatically, registers itself, walks you through logging in to WordPress and approving access, and then connects. The site must be reachable over HTTPS (cloud clients cannot reach `localhost`); for local development, expose the site through an HTTPS tunnel such as ngrok or cloudflared.

= Does this plugin store any data? =

Yes. To run the OAuth server it creates three database tables for registered clients, short-lived authorization codes, and access/refresh tokens. Tokens and client secrets are stored only as keyed hashes, never in plaintext. A single options row holds the plugin's hash salt. All of this is removed when the plugin is deleted.

= My server returns 401 even with a valid token. =

Some Apache configurations strip the `Authorization` header before it reaches PHP. Add the following to your WordPress root `.htaccess`:

`RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization}]`

= Why does the "Source" column show a namespace instead of a plugin name? =

The Abilities API does not record which plugin registered a given ability. The namespace prefix (the part before the slash in the ability name) is the most reliable indicator of where an ability comes from.

= What is the NHI Registry? =

The NHI (Non-Human Identity) Registry is where you create and manage named, role-based ability policies for AI clients. Each NHI maps WordPress roles to the abilities those roles may invoke. When an AI client makes an MCP request, the effective set of allowed abilities is the union — across every enabled NHI — of the abilities granted to the connecting user's role(s). So two users connecting the same client to the same site can see different tools, based on their roles. You can create as many NHIs as you need and toggle them on or off independently.

= How do I control which abilities an AI client can access? =

There are two levels of control. First, use the per-ability toggle on the Abilities screen to exclude an individual ability from being exposed as an MCP tool entirely — this applies regardless of any NHI policy. Second, use the NHI Registry to grant abilities per role: only the abilities mapped to the connecting user's role(s), across enabled NHIs, are reachable. The builder also flags "capability conflicts" — granting a role an ability whose required WordPress capability the role lacks — and blocks saving until they are resolved, so a grant can never silently do nothing.

= Can I disable an NHI without deleting it? =

Yes. Every NHI has an enable/disable toggle in the NHI Registry screen. A disabled NHI has no effect on MCP requests but its name and ability list are preserved, so you can re-enable it at any time without reconfiguring it.

== Changelog ==

= 1.3.1 =
* Minor fixes and reliability improvements.

= 1.3.0 =
* Execution activity log: a full audit trail of every tool call, filterable by agent, status, or time range, with per-event detail including latency and error context.
* Dashboard redesign: four focused metrics — Active Agents, Total Executions, Success Rate, and Average Latency — for an at-a-glance view of MCP server health.
* Activity timeline on the agent overview: the last 5 executions appear inline on each NHI's overview tab.
* Denied and unknown-tool calls are now correctly attributed to the responsible NHI, so the audit log is never missing an agent name.

= 1.2.3 =
* Clearer role & ability editor: a single Select all / Clear all control (instead of separate All and None buttons), role names shown in each NHI's summary, and a smoother role & ability layout.
* Refined the admin/member view switcher to a clearer segmented control.
* Fixed the support form's country picker so it no longer shifts the page or scrolls unexpectedly when opened.
* General UI polish across the NHI Registry and connection screens.

= 1.2.2 =
* NHI Registry is now role-based: grant abilities to each WordPress role, with live capability-conflict detection. A request receives the abilities its user's role(s) are granted across all enabled NHIs.
* Added a "My AI Access" member view so any logged-in user can see the tools available to their role, with an admin/member view switcher for administrators.
* Rebuilt NHI create/edit as full-screen pages (guided create wizard ending in a connection step); removed the cramped modal editor.
* Existing NHIs are migrated automatically and keep working; review each one to scope its abilities per role.
* Support and deactivation-feedback emails now include the customer's email address in the subject line.
* Added a Settings link to the plugin's row on the Plugins page.

= 1.2.1 =
* Added a floating Contact Support button, available throughout the admin app.
* Added a Setup Guide link in the toolbar for quick access to the connection guide.
* Redesigned the deactivation feedback prompt with a clearer, on-brand layout, guided reason selection, and the option to get help instead of deactivating.

= 1.2.0 =
* Added NHI Registry: a new admin screen to view and manage all non-human identity (AI client) registrations, including OAuth client details and token status.
* Added per-ability toggle to enable or disable individual abilities from being exposed as MCP tools.
* Revamped the plugin UI.

= 1.1.1 =
* Added in-plugin support form and deactivation feedback modal.

= 1.1.0 =
* Added a remote MCP server endpoint that exposes registered abilities as MCP tools.
* Added a self-hosted OAuth 2.1 authorization server with Dynamic Client Registration, PKCE, and discovery metadata so ChatGPT and Claude can connect.

= 1.0.0 =
* Initial release: read-only viewer for abilities registered through the WordPress Abilities API.

== Upgrade Notice ==

= 1.3.1 =
Minor fixes and reliability improvements. No upgrade steps required.

= 1.3.0 =
Adds a full execution activity log and a redesigned dashboard. Existing installs are migrated automatically; no manual upgrade steps are required.

= 1.2.3 =
UI refinements to the role & ability editor and general polish and fixes

= 1.2.2 =
Adds role-based access control to the NHI Registry and a member "My AI Access" view. Existing NHIs are migrated automatically and continue to work; review each one to scope its abilities per role. No manual upgrade steps are required.

= 1.2.1 =
Adds a floating Contact Support button, a quick Setup Guide link, and an improved deactivation feedback experience. No database changes or upgrade steps are required.

= 1.2.0 =
Adds the NHI Registry screen for managing connected AI clients, per-ability toggles on the Abilities screen, and a revamped plugin UI. No database changes; no upgrade steps required.

= 1.1.1 =
Adds support and deactivation feedback forms. No database changes; no upgrade steps required.

= 1.1.0 =
Introduces the OAuth-protected MCP server. The plugin now creates database tables; review the updated privacy note in the FAQ.

= 1.0.0 =
Initial release. No upgrade steps required.
