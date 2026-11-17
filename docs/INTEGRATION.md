# PostgreSQL integration boundary

The existing `kvsp1-pta1-postgresql` package was inspected, not modified or executed. `SCHEMA-FIELD-MAP.md` is a reference copy; `schema-source-hashes.json` identifies the inspected migrations. No SQL migration is required by this frontend.

## What runs today

`LogbookRepository` is the data boundary. `SessionLogbookRepository` implements it with synthetic, per-browser session records. The controller checks project assignment and role on every read/write; input is allowlisted and validated. All mutations use POST and Laravel's web CSRF middleware. Uploaded demo PDF/images use the private local disk and an assignment-checked download route. There is no public storage symlink. A 10 MB limit is a technical demo upload limit, not an academic rule. To match it when serving PHP, set `upload_max_filesize=10M` and `post_max_size=12M`.

This implementation is a functional frontend demonstration, not production authentication or durable storage. It refuses every preview route unless `LOGBOOK_DEMO=true` AND the application environment is local/testing. Do not expose it as a real student portal. Demo logout deliberately retains synthetic records for switching roles in the same browser; production logout must invalidate the authenticated session. Session drafts expire; attached files must be periodically removed from the demo private directory after sessions expire.

## Required production adapter

1. Replace the demo login with the chosen verified identity provider. Do not add Laravel's default users/password schema over the completed schema. The inspected `logbook.users` delegates authentication and has no password hash.
2. Bind an implementation of `LogbookRepository` using the approved per-person PostgreSQL connection model. The existing RLS identity is `session_user`, mapped through `users.db_principal`. A shared privileged connection, `SET ROLE`, or an arbitrary UUID session variable is not equivalent. Do not weaken RLS to make a shared connection work. Identity architecture must be reviewed before connecting.
3. Read only authorized rows, map schema-qualified tables explicitly, preserve UUIDs, and treat updates affecting zero rows as denied/stale rather than success. Split the repository's demo whole-record `save()` into transactional table-specific writes; it is not a recommendation to replace entire relational records.
4. Map `student` to `logbook.students`, two `contacts` to `student_emergency_contacts`, `project` to `pta1_projects`, thirteen `sections` to `proposal_sections`, and `meetings` to `pta1_meetings`. The exact mapping is in `SCHEMA-FIELD-MAP.md`. `course_code` and `programme_code` must remain independent. The production proposal labels can come from `proposal_section_catalog`.
5. Map evaluation drafts to `pta1_evaluations`. Populate score controls only from verified `evaluation_criteria`, in stored display order, with all five printed descriptors. Do not seed placeholder criteria. Existing final/calculated-mark constraints intentionally require NULL. The four component weights alone do not establish a formula or pass threshold.
6. Replace the unavailable signature representations only after the college agrees on an electronic-signature method. Each actor signs only their own slot. Show the exact content before attestation; store immutable private object references. After the first signature, render the schema's `content_snapshot`, enforce its content locks, and use its revision/concurrency behavior. This demo creates no signatures and must not be used as proof of an implemented attestation flow.
7. Replace demo uploads with immutable private objects, ownership checks, malware/type validation appropriate to deployment and authorized downloads. Do not allow callers to submit arbitrary storage keys. The production schema accepts an attachment bundle/manifest reference; the demo array shows multiple files without inventing academic subfields.
8. Retest using real separately authenticated student/supervisor accounts, unassigned projects, concurrent edits/signing and the exact PostgreSQL production version. The frontend test suite cannot prove database RLS, object-store authorization or real identity integration.

## Academic scope and unresolved evidence

The eight current page templates show six printed document positions plus login and assigned-project selection. The removed cover and title pages remain only in the historical source map. No dashboard, final submission gate, seven-meeting cap, additional project stages or administrator academic approval is added. Blank drafts are allowed. Names are uppercased for the two printed uppercase positions without overwriting stored names. Hari remains entered text. Times are local Asia/Kuala_Lumpur.

Students receive an access-state page for evaluation, with no evaluation values in its HTML. This follows the existing schema's conservative withholding of unreleased evaluations; final college policy is unresolved. Supervisors cannot edit student content. Proposal acknowledgements and meeting signatures retain separate student/supervisor spaces.

The original photos were not available through the retrieved conversation. Exact cover artwork, issuing authority/department wording, fine-grained rubrics, calculation and legally accepted signatures therefore remain source-audit items. No emblem or government crest was fabricated. The K mark is merely a navigation monogram. The covers are labelled previews and every print is watermarked as sample content.

## Verification logic and feedback

Rule of inference: constraint preservation by direct mapping. A field is included when present in the source specification/schema, and a missing criterion stays unimplemented. Shared stored values justify shared data, not removal of duplicate pages. Synthetic sample content is evidence of rendering only, never evidence of an official requirement.

Compare all ten screens with the physical source, especially both code fields, both emergency contacts, thirteen headings and separate signature roles. Resolve differences in the field map before connecting production. Then audit the rubric transcription independently. Alternatives such as shared database pooling need a reviewed identity adapter; merely making the UI role-aware is insufficient.

Technical references: [Laravel Blade](https://laravel.com/docs/12.x/blade), [Laravel authorization](https://laravel.com/docs/12.x/authorization), [Tailwind Vite integration](https://tailwindcss.com/docs/installation/using-vite), [Alpine installation](https://alpinejs.dev/essentials/installation), [PostgreSQL row security](https://www.postgresql.org/docs/current/ddl-rowsecurity.html).
