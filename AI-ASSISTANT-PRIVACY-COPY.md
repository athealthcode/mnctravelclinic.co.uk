# MNC Travel Clinic AI assistant privacy copy

Approved for publication alongside the v3 assistant on 29 August 2026.

## AI website assistant

AT Health Ltd, company number 14519140, is the data controller for the AI assistant operated on the MNC Travel Clinic website. Ahmed Nizar Al-Liabi is the privacy contact and can be reached at info@at-health.co.uk.

The optional assistant provides general information about opening hours, locations, services, prices, booking routes and travel health. It is not a healthcare professional, cannot access pharmacy records or bookings, and must not be used for diagnosis, personalised medical advice, medicine-suitability decisions or emergencies.

Please do not enter your name, contact details, date of birth, NHS number, medical history, symptoms or medicines. Common direct identifiers are automatically removed before a question is sent through Supabase to Anthropic's Claude API. Clinical or emergency wording is redirected to an appropriate human or NHS route. Automated safeguards cannot identify every possible disclosure.

For the normal assistant service, AT Health Ltd does not store the question or reply. It stores limited operational information for up to 30 days, including the website used, a random browser-session identifier, source page path without its query string, broad topic, controlled search-intent label, response outcome, prompt source, evidence version, feedback and selected booking or contact clicks. A separate secret-keyed network representation used only to prevent abuse is deleted after two days. Test traffic is excluded from content insights.

AT Health Ltd relies on its legitimate interests to provide, secure and improve the normal assistant and website using this minimised operational information. Detailed search-intent and destination labels are disclosed in improvement reports only after at least five distinct anonymous sessions share the same label. No chatbot information is used for advertising or joined to patient, booking or NHS records.

Visitors may separately choose to let AT Health Ltd retain an already-redacted question and reply for answer-quality review. The checkbox is optional and off by default. Where the conversation reveals health information, this processing relies on the visitor's explicit consent. Declining does not affect the normal assistant. Consented content is encrypted, restricted to authorised reviewers and automatically deleted after 30 days. Emergency and clinical-handoff conversations are never retained for QA.

An opted-in visitor receives an immediate deletion control and a private reference. The reference can be quoted when contacting info@at-health.co.uk so AT Health Ltd can locate and delete the stored conversation before its expiry. Withdrawal does not affect processing that occurred before withdrawal. Anonymous aggregate statistics that can no longer be linked to the conversation are not affected.

Supabase provides database and Edge Function infrastructure. Anthropic processes the redacted text to generate the answer. Under Anthropic's standard commercial API terms, API inputs and outputs may be retained for up to 30 days for safety and service operation and are not used to train its models unless the customer explicitly opts in. AT Health Ltd does not authorise supplier training using these conversations.

Visitors may avoid AI processing by closing the assistant and contacting MNC Travel Clinic directly. Data-protection rights and the right to complain to the Information Commissioner's Office are described elsewhere in this policy.
