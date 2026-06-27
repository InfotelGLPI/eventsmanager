# Events Manager Plugin — Documentation

## Overview

The **Events Manager** plugin for GLPI manages **IT events** (alerts, incidents, warnings, information items) independently of tickets. An event follows its own lifecycle (New → Assigned → Closed), can be enriched with threaded comments, linked to GLPI assets, then converted into a ticket or associated with existing tickets. Configurable origins (mail collector, RSS feed, REST API) allow automatic event creation.

- **Version**: 4.0.0
- **GLPI compatibility**: 11.x (< 12.0)
- **License**: GPLv3+
- **Authors**: Infotel, Xavier CAILLAUD

---

## Features

- **Event management**: create, qualify and track events with priority, impact, type and time-to-resolve
- **Lifecycle**: three statuses — New, Assigned, Closed
- **Five event types**: Undefined, Information, Warning, Exception, Alert (colour-coded)
- **Quick actions in the list**: assign yourself, create a ticket, close the event from the action icon
- **Asset association**: link any GLPI asset type to an event
- **Threaded comments**: hierarchical discussion (nested replies) with AJAX editing
- **Ticket integration**: create a ticket from an event (field inheritance), link existing tickets, view events from the ticket form
- **Automatic close**: option to close the event automatically when a ticket is created from it
- **Origins**: qualify the source of an event (mail collector, RSS feed, REST API, others)
- **E-mail import**: tab on GLPI mail collectors to create events via mail collector rules
- **RSS import**: tab on GLPI RSS feeds, automatic import cron task
- **Knowledge base**: associate knowledge base articles with an event
- **Documents**: attachments transferred to the ticket on creation
- **Massive action**: transfer events between entities
- **MyDashboard integration**: alert widget if the MyDashboard plugin is installed
- **Simplified interface**: access to events in the helpdesk menu (right `plugin_eventsmanager`)

---

## Prerequisites and installation

1. Download the archive from [GitHub Releases](https://github.com/InfotelGLPI/eventsmanager/releases).
2. Extract it into the `plugins/` directory of your GLPI installation.
3. Log in to GLPI as an administrator.
4. Go to **Configuration → Plugins** and activate **Events Manager**.

---

## Rights

Events Manager uses a **single right**:

| Right | Description |
|---|---|
| `plugin_eventsmanager` | Access to all features (read, create, update, delete, purge) |

This right is assigned under **Administration → Profiles**, **Events Manager** tab. Standard GLPI rights apply: `READ`, `CREATE`, `UPDATE`, `DELETE`, `PURGE`.

The plugin configuration page requires the GLPI `config` right with UPDATE permission.

---

## Global configuration

**Access**: **Configuration → Plugins → Events Manager (Configure)**

| Setting | Description |
|---|---|
| Automatically close event when creating a ticket | If enabled, the event status is automatically set to Closed as soon as a ticket is created from that event |

---

## Events

**Access**: **Helpdesk → Events** (simplified interface) or main menu (central interface)

### Event fields

| Field | Description |
|---|---|
| Name | Event title |
| Impact | Impact level (same scale as GLPI tickets: very low → very high) |
| Priority | Priority level (same scale as GLPI tickets) |
| Event type | Undefined / Information / Warning / Exception / Alert |
| Origin | Qualified source (mail collector, RSS feed, REST API, others) |
| Assigned user | Technician responsible for the event |
| Time to resolve | Deadline for resolution |
| Status | New / Assigned / Closed |
| Description | Rich content (HTML editor) |
| Associated element | GLPI asset linked to the event |

### Statuses

| Status | Constant | Description |
|---|---|---|
| New | `NEW_STATE = 1` | Event created, not yet handled |
| Assigned | `ASSIGNED_STATE = 2` | Technician designated (automatically set if a user is assigned at creation) |
| Closed | `CLOSED_STATE = 3` | Event handled and closed |

### Event types and colours

| Type | Background colour |
|---|---|
| Undefined | Light grey |
| Information | Light green |
| Warning | Blue (CornflowerBlue) |
| Exception | Orange |
| Alert | Red |

### Quick actions

From the event list and from the event form (status not Closed):

| Icon | Action |
|---|---|
| User+ icon | Assign yourself to the event (AJAX) |
| Bell icon | Create a ticket from the event |
| Archive icon | Close the event (AJAX) |

---

## Origins

**Access**: Dropdown on the event form

An **origin** qualifies the source of an event. It combines a **source type** and an **element** of that type:

| Type | Associated element |
|---|---|
| Mail collector (`Collector`) | GLPI mail collector |
| RSS feed (`RSS`) | GLPI RSS feed |
| REST API (`Api`) | None |
| Others (`Others`) | None |

Each origin can also be associated with a GLPI **request type**, which will be applied when a ticket is automatically created from that event.

---

## Ticket integration

### Create a ticket from an event

From the form of an open event, the **Tickets** tab offers:
- **Create a new ticket**: pre-fills the ticket with the event's name, description, priority, impact, time to resolve, and entity. Documents and assets associated with the event are also transferred to the ticket.
- **Link an existing ticket**: associate an already existing ticket with the event.

If **automatic close** is enabled in the configuration, the event moves to Closed status as soon as the ticket is created.

### View events from a ticket

A **Linked events** tab appears on each GLPI ticket form, listing the associated events with their status, priority, type, and linked assets.

---

## E-mail import

**Access**: **Configuration → Mail collectors → (collector form) → Events Manager tab**

Configures the default values applied when an event is automatically created via GLPI mail collector rules:

| Setting | Description |
|---|---|
| Default impact | Impact applied to the created event |
| Default priority | Priority applied |
| Default event type | Event type applied |

The actual creation is triggered by a **GLPI mail collector rule** using the action **"Assign an entity to create an event"** provided by the plugin.

---

## RSS import

**Access**: **Tools → RSS feeds → (RSS feed form) → Events Manager tab**

Enables automatic import of articles from a GLPI RSS feed as events:

| Setting | Description |
|---|---|
| Use this feed to create events | Yes / No |
| Target entity | Entity in which events are created |
| Default impact | Impact applied |
| Default priority | Priority applied |
| Default event type | Event type applied |

The **`RssImport`** cron task (Events Manager) runs periodically and creates one event per new article in the feed (since the last imported article).

---

## Comments

From the event form, the **Comments** tab displays a hierarchical threaded discussion:
- Add a comment
- Reply to a comment (nesting)
- Edit your own comment (AJAX)

---

## MyDashboard integration

If the **MyDashboard** plugin is installed, an **Alert** widget can be added to the dashboard from the event form (dedicated tab).

---

## Database schema

| Table | Description |
|---|---|
| `glpi_plugin_eventsmanager_events` | Events (name, status, priority, impact, type, origin, dates) |
| `glpi_plugin_eventsmanager_origins` | Configured origins (source type, associated element, request type) |
| `glpi_plugin_eventsmanager_events_items` | Assets linked to an event |
| `glpi_plugin_eventsmanager_tickets` | Links between events and GLPI tickets |
| `glpi_plugin_eventsmanager_events_comments` | Threaded comments on an event |
| `glpi_plugin_eventsmanager_mailimports` | E-mail import configuration per collector |
| `glpi_plugin_eventsmanager_rssimports` | RSS import configuration per feed |
| `glpi_plugin_eventsmanager_configs` | Global plugin configuration |

---

## Uninstallation

In **Configuration → Plugins**, deactivate then uninstall **Events Manager**. All `glpi_plugin_eventsmanager_*` tables are dropped, along with the associated profile rights and cron tasks.

---

## Useful links

- [GitHub repository](https://github.com/InfotelGLPI/eventsmanager)
- [Report a bug](https://github.com/InfotelGLPI/eventsmanager/issues)
- [Contribute translations](https://explore.transifex.com/infotelGLPI/GLPI_eventsmanager/)
- [Infotel GLPI blog](https://blogglpi.infotel.com)
