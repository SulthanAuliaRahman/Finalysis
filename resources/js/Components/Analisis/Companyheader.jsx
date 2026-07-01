import { Building2, FileText, Calendar } from 'lucide-react';

const documents = [
    {
        id: 1,
        name: 'Laporan Laba Rugi Q1 2026',
        uploadDate: '2026-04-15',
        status: 'analyzed',
    },
    {
        id: 2,
        name: 'Neraca Keuangan Q1 2026',
        uploadDate: '2026-04-15',
        status: 'analyzed',
    },
    {
        id: 3,
        name: 'Arus Kas Q1 2026',
        uploadDate: '2026-04-16',
        status: 'analyzed',
    },
];

export function CompanyHeader() {
    return (
        <div className="bg-white border border-slate-200 rounded-xl p-6 mb-8 shadow-xs">
            <div className="flex items-start gap-4">
                <div className="p-3 bg-blue-50 rounded-lg">
                    <Building2 className="w-8 h-8 text-blue-600" />
                </div>
                <div className="flex-1">
                    <h2 className="text-2xl font-semibold text-slate-900 mb-2">PT Maju Jaya Sentosa</h2>
                    <p className="text-slate-500 text-sm mb-4">
                        Perusahaan manufaktur yang bergerak di bidang produksi komponen elektronik dan peralatan industri.
                        Berdiri sejak 2010 dengan fokus pada inovasi teknologi dan kualitas produk untuk pasar domestik dan ekspor.
                    </p>

                    <div className="border-t border-slate-100 pt-4">
                        <div className="flex items-center gap-2 mb-3">
                            <FileText className="w-4 h-4 text-slate-400" />
                            <h3 className="text-sm font-medium text-slate-900">Dokumen Laporan Keuangan</h3>
                            <span className="text-xs bg-slate-100 text-slate-600 px-2 py-0.5 rounded">
                                {documents.length} dokumen
                            </span>
                        </div>

                        <div className="grid grid-cols-1 md:grid-cols-3 gap-3">
                            {documents.map((doc) => (
                                <div
                                    key={doc.id}
                                    className="flex items-center gap-3 bg-slate-50/70 rounded-lg p-3 border border-slate-100"
                                >
                                    <div className="p-2 bg-blue-50 rounded">
                                        <FileText className="w-4 h-4 text-blue-600" />
                                    </div>
                                    <div className="flex-1 min-w-0">
                                        <p className="text-sm font-medium text-slate-900 truncate">{doc.name}</p>
                                        <div className="flex items-center gap-1 mt-0.5">
                                            <Calendar className="w-3 h-3 text-slate-400" />
                                            <p className="text-xs text-slate-400">{doc.uploadDate}</p>
                                        </div>
                                    </div>
                                    <div className="flex-shrink-0">
                                        <span className="inline-block w-2 h-2 bg-green-500 rounded-full"></span>
                                    </div>
                                </div>
                            ))}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    );
}
