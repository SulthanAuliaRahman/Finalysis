import { Building2, FileText} from 'lucide-react';

export function CompanyHeader({ perusahaan, dokumenPeriode }) {
    return (
        <div className="bg-white border border-slate-200 rounded-xl p-6 mb-8 shadow-xs">
            <div className="flex items-start gap-4">
                <div className="p-3 bg-blue-50 rounded-lg">
                    <Building2 className="w-8 h-8 text-blue-600" />
                </div>
                <div className="flex-1">
                    <h2 className="text-2xl font-semibold text-slate-900 mb-2">{perusahaan.nama}</h2>
                    <p className="text-slate-500 text-sm mb-4">
                        {perusahaan.profile || 'Belum ada deskripsi untuk perusahaan ini.'}
                    </p>

                    <div className="border-t border-slate-100 pt-4">
                        <div className="flex items-center gap-2 mb-3">
                            <FileText className="w-4 h-4 text-slate-400" />
                            <h3 className="text-sm font-medium text-slate-900">Dokumen Laporan Keuangan Periode Ini</h3>
                        </div>

                        {!dokumenPeriode ? (
                            <p className="text-xs text-slate-400 italic">
                                Tidak ada dokumen ditemukan untuk periode ini.
                            </p>
                        ) : (
                            <div
                                key={dokumenPeriode.id}
                                className="flex bg-slate-50/70 rounded-lg p-3 border border-slate-100 max-w-md"
                            >
                                <div className="p-2 bg-blue-50 rounded">
                                    <FileText className="w-4 h-4 text-blue-600" />
                                </div>

                                <div className="min-w-0">
                                    <p className="text-sm font-medium text-slate-900 truncate">
                                        {dokumenPeriode.nama_file}
                                    </p>
                                </div>
                            </div>
                        )}
                    </div>
                </div>
            </div>
        </div>
    );
}
