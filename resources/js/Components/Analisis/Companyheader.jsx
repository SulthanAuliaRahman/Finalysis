import { Building2, FileText, Calendar } from 'lucide-react';

const STATUS_DOT_COLOR = {
    menunggu: 'bg-slate-300',
    diekstrak: 'bg-blue-400',
    dichunk: 'bg-amber-400',
    diembed: 'bg-indigo-400',
    selesai: 'bg-green-500',
};

export function CompanyHeader({ perusahaan, dokumenPeriode = [] }) {
    return (
        <div className="bg-white border border-slate-200 rounded-xl p-6 mb-8 shadow-xs">
            <div className="flex items-start gap-4">
                <div className="p-3 bg-blue-50 rounded-lg">
                    <Building2 className="w-8 h-8 text-blue-600" />
                </div>
                <div className="flex-1">
                    <h2 className="text-2xl font-semibold text-slate-900 mb-2">{perusahaan.nama}</h2>
                    <p className="text-slate-500 text-sm mb-4">
                        {perusahaan.deskripsi || 'Belum ada deskripsi untuk perusahaan ini.'}
                    </p>

                    <div className="border-t border-slate-100 pt-4">
                        <div className="flex items-center gap-2 mb-3">
                            <FileText className="w-4 h-4 text-slate-400" />
                            <h3 className="text-sm font-medium text-slate-900">Dokumen Laporan Keuangan Periode Ini</h3>
                            <span className="text-xs bg-slate-100 text-slate-600 px-2 py-0.5 rounded">
                                {dokumenPeriode.length} dokumen
                            </span>
                        </div>

                        {dokumenPeriode.length === 0 ? (
                            <p className="text-xs text-slate-400 italic">
                                Tidak ada dokumen ditemukan untuk periode ini.
                            </p>
                        ) : (
                            <div className="grid grid-cols-1 md:grid-cols-3 gap-3">
                                {dokumenPeriode.map((doc) => (
                                    <div
                                        key={doc.id}
                                        className="flex items-center gap-3 bg-slate-50/70 rounded-lg p-3 border border-slate-100"
                                    >
                                        <div className="p-2 bg-blue-50 rounded">
                                            <FileText className="w-4 h-4 text-blue-600" />
                                        </div>
                                        <div className="flex-1 min-w-0">
                                            <p className="text-sm font-medium text-slate-900 truncate">{doc.nama_file}</p>
                                            <div className="flex items-center gap-1 mt-0.5">
                                                <Calendar className="w-3 h-3 text-slate-400" />
                                                <p className="text-xs text-slate-400">
                                                    {new Date(doc.created_at).toLocaleDateString('id-ID', {
                                                        day: 'numeric', month: 'long', year: 'numeric'
                                                    })}
                                                </p>
                                            </div>
                                        </div>
                                        <div className="flex-shrink-0">
                                            <span className={`inline-block w-2 h-2 rounded-full ${STATUS_DOT_COLOR[doc.status] || 'bg-slate-300'}`}></span>
                                        </div>
                                    </div>
                                ))}
                            </div>
                        )}
                    </div>
                </div>
            </div>
        </div>
    );
}
