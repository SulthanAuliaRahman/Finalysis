import { useState } from 'react';
import axios from 'axios';
import { BookOpen, ExternalLink, FileText, Loader2, X } from 'lucide-react';

export function ReferenceButton({ documents = [], section }) {
    const [isOpen, setIsOpen] = useState(false);
    const [selectedDocument, setSelectedDocument] = useState(null);
    const [chunks, setChunks] = useState([]);
    const [isLoadingChunks, setIsLoadingChunks] = useState(false);
    const [chunksError, setChunksError] = useState('');

    async function showChunks(document) {
        setSelectedDocument(document);
        setChunks([]);
        setChunksError('');
        setIsLoadingChunks(true);

        try {
            const response = await axios.get(document.chunks_url, { params: { section } });
            setChunks(response.data.chunks ?? []);
        } catch {
            setChunksError('Gagal memuat potongan teks referensi.');
        } finally {
            setIsLoadingChunks(false);
        }
    }

    function closeModal() {
        setIsOpen(false);
        setSelectedDocument(null);
        setChunks([]);
        setChunksError('');
    }

    return (
        <>
            <button
                type="button"
                onClick={() => setIsOpen(true)}
                className="flex items-center gap-1.5 px-2.5 py-1 border border-slate-200 rounded-lg text-slate-500 hover:bg-slate-50 transition-colors text-xs"
            >
                <BookOpen className="w-3.5 h-3.5" />
                View Referensi
            </button>

            {isOpen && (
                <div className="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/40 p-4" role="dialog" aria-modal="true" aria-label="Referensi RAG">
                    <div className="w-full max-w-lg max-h-[80vh] overflow-hidden rounded-xl bg-white shadow-xl">
                        <div className="flex items-start justify-between border-b border-slate-100 p-5">
                            <div>
                                <h4 className="text-sm font-semibold text-slate-900">Referensi Analisis</h4>
                                <p className="mt-1 text-xs text-slate-500">Dokumen yang telah diindeks sebagai sumber pengetahuan AI untuk perusahaan ini.</p>
                                {documents.length > 0 && (
                                    <button type="button" onClick={() => showChunks(documents[0])} className="mt-2 text-xs font-medium text-blue-600 hover:text-blue-700">
                                        Lihat chunks yang dipakai analisis
                                    </button>
                                )}
                            </div>
                            <button type="button" onClick={closeModal} className="text-slate-400 hover:text-slate-700" aria-label="Tutup referensi">
                                <X className="h-4 w-4" />
                            </button>
                        </div>

                        <div className="max-h-[32vh] space-y-3 overflow-y-auto p-5">
                            {documents.length === 0 ? (
                                <p className="rounded-lg border border-dashed border-slate-200 bg-slate-50 p-4 text-center text-xs text-slate-500">
                                    Belum ada dokumen referensi yang terindeks.
                                </p>
                            ) : documents.map((document) => (
                                <div key={document.id} className="rounded-lg border border-slate-200 p-3">
                                    <div className="flex gap-2">
                                        <FileText className="mt-0.5 h-4 w-4 shrink-0 text-blue-600" />
                                        <div className="min-w-0 flex-1">
                                            <p className="truncate text-xs font-medium text-slate-800" title={document.nama_file}>{document.nama_file}</p>
                                            <p className="mt-0.5 text-[11px] text-slate-500">{document.periode_label} - {document.chunks_count} potongan teks</p>
                                            <div className="mt-2 flex flex-wrap gap-3">
                                                <button type="button" onClick={() => showChunks(document)} className="inline-flex items-center gap-1 text-[11px] font-medium text-blue-600 hover:text-blue-700">
                                                    Lihat chunks
                                                </button>
                                                <a href={document.pdf_url} target="_blank" rel="noreferrer" className="inline-flex items-center gap-1 text-[11px] font-medium text-blue-600 hover:text-blue-700">
                                                    Lihat PDF <ExternalLink className="h-3 w-3" />
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            ))}
                        </div>

                    </div>
                </div>
            )}

            {selectedDocument && (
                <div className="fixed inset-0 z-[60] flex items-center justify-center bg-slate-900/50 p-4" role="dialog" aria-modal="true" aria-label="Potongan teks referensi">
                    <div className="w-full max-w-3xl overflow-hidden rounded-xl bg-white shadow-xl">
                        <div className="flex items-start justify-between border-b border-slate-100 p-5">
                            <div className="min-w-0">
                                <h4 className="text-sm font-semibold text-slate-900">Potongan Teks Relevan</h4>
                                <p className="mt-1 truncate text-xs text-slate-500">Chunks yang dipakai saat analisis dibuat.</p>
                                <p className="mt-1 text-[11px] text-blue-600">
                                    {selectedDocument ? `Sumber dokumen: ${selectedDocument.nama_file}` : 'Hasil retrieval dan reranking yang tersimpan untuk analisis ini (maksimal 5 chunks).'}
                                </p>
                            </div>
                            <button type="button" onClick={() => { setSelectedDocument(null); setChunks([]); setChunksError(''); }} className="text-slate-400 hover:text-slate-700" aria-label="Tutup potongan teks">
                                <X className="h-4 w-4" />
                            </button>
                        </div>

                        <div className="max-h-[65vh] space-y-3 overflow-y-auto bg-slate-50 p-5">
                            {isLoadingChunks ? (
                                <div className="flex items-center justify-center gap-2 py-10 text-sm text-slate-500"><Loader2 className="h-4 w-4 animate-spin" /> Memuat dan mereranking potongan teks...</div>
                            ) : chunksError ? (
                                <p className="rounded-lg border border-red-100 bg-red-50 p-4 text-center text-xs text-red-600">{chunksError}</p>
                            ) : chunks.length === 0 ? (
                                <p className="rounded-lg border border-dashed border-slate-200 bg-white p-4 text-center text-xs text-slate-500">Referensi belum tersimpan untuk analisis ini. Klik Regenerasi untuk membuat ulang analisis dan menyimpan chunks yang dipakai.</p>
                            ) : chunks.map((chunk, index) => (
                                <article key={chunk.id} className="rounded-lg border border-slate-200 bg-white p-4">
                                    <div className="mb-2 flex items-center justify-between gap-3">
                                        <p className="text-[10px] font-semibold uppercase tracking-wide text-slate-400">
                                            Referensi {index + 1}{chunk.chunk_index !== null ? ` · Chunk ${chunk.chunk_index}` : ''}
                                        </p>
                                        <span className="text-[10px] text-slate-400">Skor: {Number(chunk.score).toFixed(3)}</span>
                                    </div>
                                    {chunk.source_file && (
                                        <p className="mb-2 text-[11px] font-medium text-blue-700">
                                            Sumber: {chunk.source_file}
                                        </p>
                                    )}
                                    <p className="whitespace-pre-line text-xs leading-relaxed text-slate-600">{chunk.text}</p>
                                </article>
                            ))}
                        </div>
                    </div>
                </div>
            )}
        </>
    );
}
