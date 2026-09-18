export const DESCRIPTION_SECTIONS = [
    { key: "identitas", label: "Profil & skala usaha", placeholder: "Contoh: tahun berdiri, lokasi, jumlah karyawan, dan skala usaha." },
    { key: "bidang", label: "Bidang dan jasa utama", placeholder: "Produk atau jasa utama yang ditawarkan." },
    { key: "pendapatan", label: "Model pendapatan & pola pembayaran", placeholder: "Sumber pendapatan, pelanggan utama, termin pembayaran, dan kebiasaan penagihan." }
];

// Format sebelumnya tetap dapat dibuka tanpa menghilangkan informasi.
const LEGACY_SECTION_MAP = {
    "Identitas & ukuran usaha": "identitas",
    "Profil & skala usaha": "identitas",
    "Bidang dan jasa utama": "bidang",
    "Model pendapatan": "pendapatan",
    "Pelanggan & pola pembayaran": "pendapatan",
    "Model pendapatan & pola pembayaran": "pendapatan",
    "Operasional & sumber daya": null,
    "Operasional, aset & pembiayaan": null,
    "Aset dan pembiayaan utama": null,
    "Peristiwa penting selama periode": null,
    "Peristiwa penting & catatan pemilik": null,
    "Catatan tambahan dari pemilik": null
};

export const emptyDescriptionSections = () =>
    Object.fromEntries(DESCRIPTION_SECTIONS.map(({ key }) => [key, ""]));

export function parseDescription(description = "") {
    const sections = emptyDescriptionSections();
    const labelToKey = Object.fromEntries(DESCRIPTION_SECTIONS.map(({ key, label }) => [label, key]));
    Object.assign(labelToKey, LEGACY_SECTION_MAP);
    const labels = Object.keys(labelToKey).map(label => label.replace(/[.*+?^${}()|[\]\\]/g, "\\$&"));
    const matcher = new RegExp(`(?:^|\\n)\\*{0,2}(${labels.join("|")})\\*{0,2}\\s*\\n`, "g");
    const matches = [...description.matchAll(matcher)];

    if (!matches.length) {
        // Deskripsi lama tanpa judul bagian diperlakukan sebagai bidang/jasa utama.
        sections.bidang = description;
        return sections;
    }

    const preamble = description.slice(0, matches[0].index).trim();
    if (preamble) sections.bidang = preamble;

    matches.forEach((match, index) => {
        const contentStart = match.index + match[0].length;
        const contentEnd = index + 1 < matches.length ? matches[index + 1].index : description.length;
        const sectionKey = labelToKey[match[1]];
        const content = description.slice(contentStart, contentEnd).trim();

        if (sectionKey) {
            sections[sectionKey] = sections[sectionKey]
                ? `${sections[sectionKey]}\n\n${content}`
                : content;
        }
    });

    return sections;
}

export function serializeDescription(sections) {
    return DESCRIPTION_SECTIONS
        .filter(({ key }) => sections[key].trim())
        .map(({ key, label }) => `${label}\n${sections[key].trim()}`)
        .join("\n\n");
}
