import { FileText } from "lucide-react";

export default function PdfViewer({ fileUrl, title = "Pratinjau Dokumen PDF" }) {
    return (
        <div className="bg-white border border-slate-200 rounded-xl overflow-hidden shadow-xs flex flex-col h-[700px]">
            <div className="bg-slate-50 border-b border-slate-200 px-4 py-3 flex items-center gap-2">
                <FileText className="w-4 h-4 text-slate-500" />
                <h3 className="text-sm font-semibold text-slate-700">{title}</h3>
            </div>

            {/* Menggunakan iframe untuk merender PDF bawaan browser */}
            <div className="flex-grow w-full bg-slate-100/50">
                {fileUrl ? (
                    <iframe
                        src={`${fileUrl}#toolbar=0&navpanes=0`}
                        className="w-full h-full border-none"
                        title={title}
                    />
                ) : (
                    <div className="flex items-center justify-center h-full text-sm text-slate-400">
                        URL file PDF tidak tersedia.
                    </div>
                )}
            </div>
        </div>
    );
}
