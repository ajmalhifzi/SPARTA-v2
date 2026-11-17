# Printed-field traceability

Source: [Photo Analysis Summary](chatgpt-conversation://6ae961bf-0f1c-8329-8021-d2b1f09adfba), specifically its Phase 1 inventory, digital form specification and Phase 3 14-table design. Read on 2026-11-03. The retrieved source contains no image attachments; exact page artwork and fine-grained rubric content still require comparison with the originals. The user's acceptance of REQ-011–013 is preserved as requirements acceptance; it does not supply absent detailed criterion values.

## Document mapping

| Printed document / field | Database or rendering source |
| --- | --- |
| DOC-01 official cover, authority, department, title, edition | Static page asset/template; no invented data-entry table. Obtain exact artwork/text from original. |
| DOC-02 Nama | `students.full_name` |
| No. Kad Pengenalan | `students.identity_number` (text, preserving formatting/leading zeros) |
| Tahun / Semester | `students.year_semester` (verbatim text); project has structured `semester = 3` |
| Program | `students.programme` |
| No. Telefon | `students.telephone` |
| Alamat | `students.address` |
| Nama Penyelia Projek | Relevant project's `supervisor_id` → `supervisors.full_name` |
| Nama Kolej Vokasional | `students.college_name` |
| Alamat Kolej | `students.college_address` |
| No. Telefon Kolej | `students.college_telephone` |
| Emel Kolej | `students.college_email` |
| Two emergency-contact sets: Nama, No. Telefon, Pertalian | `student_emergency_contacts.name`, `.telephone`, `.relationship`; contact slots 1 and 2, unique per student |
| DOC-03 Nama Program | `pta1_projects.programme_name` |
| Kod Kursus | `pta1_projects.course_code` |
| Tajuk Projek | `pta1_projects.project_title` |
| Nama Pelajar (Dengan Huruf Besar) | Linked student's full name; uppercase display in this field, not destructive rewriting of stored name |
| No. Kad Pengenalan | Linked student's identity number |
| Nama Penyelia Projek | Linked supervisor's full name |
| Perkara / Ringkasan | Fixed section catalog plus `proposal_sections.content`; all 13 positions initialized |
| Additional sheets instruction | Keep the printed instruction; unrestricted narrative text plus private attachment reference |
| Perakuan Pelajar: Nama Pelajar, Tandatangan, Tarikh, H/P, R, Email | Student row in `proposal_signatures`: `printed_name`, `signature_reference`, `signed_date`, `mobile_telephone`, `home_telephone`, `email` |
| Pengesahan Penyelia Projek: Nama Pegawai, Tandatangan, Tarikh, Cop Nama Pegawai | Supervisor row in `proposal_signatures`: `printed_name`, `signature_reference`, `signed_date`, `officer_stamp_reference` |
| DOC-04 Buku Log Projek Tahun Akhir 1 title | Separate static title page/template |
| DOC-05 Nama Program | `pta1_projects.programme_name` in its own printed position |
| Kod Program | `pta1_projects.programme_code`, **not** `course_code` |
| Tajuk Projek, Nama Pelajar, No. Kad Pengenalan, Nama Penyelia Projek | Shared project/profile values rendered again in the DOC-05 table |
| DOC-06 Perjumpaan / Tarikh / Aktiviti | `pta1_meetings.meeting_number`, `.meeting_date`, `.activity` |
| DOC-07 Tarikh / Hari / Masa | Same meeting `.meeting_date`, `.day_label`, `.meeting_time`; preserve entered Hari rather than silently translating it |
| Nama Penyelia Projek / Jabatan / Program | Meeting-specific `.supervisor_name`, `.department_programme`, retained historically |
| Ringkasan Perbincangan / Aktiviti | `.discussion_summary` |
| Pelan Tindakan Perbincangan Akan Datang | `.next_action_plan` |
| Tandatangan Pelajar / Tandatangan Penyelia Projek (PP) | Two separate rows in `meeting_signatures`, limited to their respective actors |
| DOC-08 four assessment components / weights | `evaluation_components`, immutable seeded reference |
| Exact criterion text / printed reference / weight / five descriptors / outcomes | `evaluation_criteria`; deliberately empty until verified transcription |
| Criterion rating / calculated mark | `evaluation_scores.rating` / `.calculated_mark`; rating 1–5 or NULL draft; calculated mark gated |
| Final marks / Terampil or Tidak Terampil / explanation | `pta1_evaluations.final_mark`, `.competency_decision`, `.decision_explanation`; final mark gated, decisions draft |
| Supervisor Nama / No. Kad Pengenalan / Tandatangan | `evaluation_confirmation.supervisor_name`, `.identity_number`, `.signature_reference` |
| Final confirmation date | `.confirmation_date` is nullable; print only if the original form includes it |

The schedule and detail share a meeting entity without removing either form. Repeated name fields share stored values where the original design explicitly allowed that; the renderer still displays every occurrence. Proposal and meeting signatures store snapshots to prevent later profile/content edits from changing the signed representation.

## All 13 proposal positions

1. Pengenalan Projek
2. Pernyataan Masalah
3. Objektif Projek
4. Tujuan Projek
5. Skop Projek
6. Literatur
7. Metodologi
8. Anggaran Kos
9. Perancangan Pelaksanaan Projek
10. Carta Gantt Pelaksanaan Projek — Rujuk Lampiran
11. Jangkaan Hasil
12. Rujukan
13. Lampiran

The fixed `proposal_section_catalog` view supplies labels. Students edit only content/attachment references, never numbers or labels. Preserve “Sila gunakan helaian tambahan jika ruangan tidak mencukupi.” in the rendering template. A reference can point to an immutable private attachment bundle/manifest when multiple continuation sheets are needed; no arbitrary one-sheet academic restriction is implied.

## All 14 tables

`users`, `students`, `supervisors`, `student_emergency_contacts`, `pta1_projects`, `proposal_sections`, `proposal_signatures`, `pta1_meetings`, `meeting_signatures`, `evaluation_components`, `evaluation_criteria`, `pta1_evaluations`, `evaluation_scores`, `evaluation_confirmation`.

Two constant-only views supply section labels and general scale labels; they add no base tables and expose no personal data. `users.password_hash` is deliberately omitted because authentication is delegated, an option explicitly contemplated by the source design. Added identity mappings, active flags, revisions, snapshots, verification provenance and timestamps are technical controls, not extra printed academic fields.

## What the schema deliberately does not infer

No seven-meeting cap, minimum number of meetings, competency pass threshold, project-count limit per student, mathematical equivalence between course/programme codes, compulsory draft completeness, automatic formula, extra administrator academic approval, legally approved electronic-signature method, or PTA 2 content is invented.

The supervisor profile and final-confirmation identity fields remain separate. The meeting supervisor name is a historical text entry and does not confer authority: authorization always comes from the project's actual assignment. Choosing which project to render for a student with multiple records is the caller's responsibility; do not arbitrarily select the first project.
