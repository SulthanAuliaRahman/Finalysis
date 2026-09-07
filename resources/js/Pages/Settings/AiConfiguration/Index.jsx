import { useState } from "react";
import { Link, router } from "@inertiajs/react";
import AppLayout from "@/Layouts/AppLayout";
import { Button } from "@/Components/ui/button";
import {
	ArrowLeft,
	BrainCircuit,
	CheckCircle2,
	Edit3,
	Plus,
	Power,
	Trash2,
	Info,
} from "lucide-react";

function StatusBadge({ isActive }) {
	if (isActive) {
		return (
			<span className="inline-flex items-center gap-1 text-[11px] font-semibold px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200">
				<span className="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse" />
				Aktif
			</span>
		);
	}

	return (
		<span className="inline-flex items-center gap-1 text-[11px] font-medium px-2 py-0.5 rounded-full bg-slate-100 text-slate-600 border border-slate-200">
			Standby
		</span>
	);
}

function ProviderBadge({ provider }) {
	const badges = {
		gemini: { label: "Google Gemini", className: "bg-blue-50 text-blue-700 border-blue-200" },
		openai: { label: "OpenAI", className: "bg-emerald-50 text-emerald-700 border-emerald-200" },
		anthropic: { label: "Anthropic", className: "bg-amber-50 text-amber-700 border-amber-200" },
		ollama: { label: "Ollama (Lokal)", className: "bg-purple-50 text-purple-700 border-purple-200" },
	};

	const { label, className } = badges[provider] || {
		label: provider,
		className: "bg-slate-50 text-slate-700 border-slate-200",
	};

	return (
		<span className={`inline-flex items-center text-[10px] font-semibold px-2 py-0.5 rounded-md border ${className}`}>
			{label}
		</span>
	);
}

function ConfigCard({ config, isOnly }) {
	const [confirmDelete, setConfirmDelete] = useState(false);

	function handleActivate() {
		router.post(`/settings/ai/${config.id}/activate`, {}, {
			preserveScroll: true,
		});
	}

	function handleDelete() {
		if (!confirmDelete) {
			setConfirmDelete(true);
			return;
		}
		router.delete(`/settings/ai/${config.id}`, {
			preserveScroll: true,
		});
	}

	const cardBorder = config.is_active
		? "border-emerald-300 ring-1 ring-emerald-200 shadow-sm"
		: "border-slate-200 hover:border-slate-300";

	return (
		<div className={`bg-white border rounded-xl overflow-hidden transition-all ${cardBorder}`}>
			{/* Header */}
			<div className={`flex items-start gap-3 p-5 border-b ${config.is_active ? "bg-emerald-50/40 border-emerald-100" : "bg-slate-50/70 border-slate-100"}`}>
				<div className={`p-2 rounded-lg border ${
					config.is_active
						? "bg-emerald-100/80 border-emerald-200 text-emerald-700"
						: "bg-white border-slate-200 text-slate-500"
				}`}>
					<BrainCircuit className="w-4 h-4" />
				</div>
				<div className="min-w-0 flex-1">
					<div className="flex items-center gap-2 flex-wrap">
						<h3 className="text-sm font-bold text-slate-900">{config.name}</h3>
						<StatusBadge isActive={config.is_active} />
					</div>
					<div className="flex items-center gap-2 mt-1.5">
						<ProviderBadge provider={config.llm_provider} />
						<span className="text-xs text-slate-500 font-mono">{config.llm_model}</span>
					</div>
				</div>
			</div>

			{/* Details */}
			<div className="p-5 space-y-3">
				<div className="grid grid-cols-2 gap-3 text-xs">
					<div>
						<span className="font-semibold text-slate-500 uppercase tracking-wide block mb-0.5">Provider</span>
						<span className="text-slate-900 font-medium capitalize">{config.llm_provider}</span>
					</div>
					<div>
						<span className="font-semibold text-slate-500 uppercase tracking-wide block mb-0.5">Model</span>
						<span className="text-slate-900 font-mono text-[13px]">{config.llm_model}</span>
					</div>
					<div className="col-span-2">
						<span className="font-semibold text-slate-500 uppercase tracking-wide block mb-0.5">
							{config.llm_provider === "ollama" ? "Base URL" : "API Key"}
						</span>
						<span className="text-slate-700 font-mono text-xs">
							{config.llm_provider === "ollama"
								? (config.base_url || <span className="text-slate-400">Default (http://localhost:11434)</span>)
								: (config.llm_api_key ? "••••••••••••••••" : <span className="text-slate-400">Tidak diset</span>)
							}
						</span>
					</div>
				</div>
			</div>

			{/* Actions */}
			<div className="flex items-center gap-2 px-5 py-3 border-t border-slate-100 bg-slate-50/50">
				{!config.is_active ? (
					<Button
						variant="outline"
						size="sm"
						onClick={handleActivate}
						className="text-emerald-700 border-emerald-200 hover:bg-emerald-50 hover:text-emerald-800"
					>
						<Power className="w-3.5 h-3.5 mr-1" /> Aktifkan
					</Button>
				) : (
					<span className="inline-flex items-center text-xs font-medium text-emerald-700 gap-1 px-1">
						<CheckCircle2 className="w-3.5 h-3.5" /> Konfigurasi Aktif
					</span>
				)}

				<Link href={`/settings/ai/${config.id}/edit`}>
					<Button variant="outline" size="sm">
						<Edit3 className="w-3.5 h-3.5 mr-1" /> Edit
					</Button>
				</Link>

				<div className="flex-1" />

				{!config.is_active && !isOnly && (
					<Button
						variant={confirmDelete ? "destructive" : "outline"}
						size="sm"
						onClick={handleDelete}
						onBlur={() => setConfirmDelete(false)}
						className={!confirmDelete ? "text-red-600 border-red-200 hover:bg-red-50" : ""}
					>
						<Trash2 className="w-3.5 h-3.5 mr-1" />
						{confirmDelete ? "Yakin hapus?" : "Hapus"}
					</Button>
				)}
			</div>
		</div>
	);
}

export default function Index({ configurations = [] }) {
	const hasConfigs = Array.isArray(configurations) && configurations.length > 0;

	return (
		<div className="max-w-5xl mx-auto space-y-4">
			<Link href="/dashboard" className="inline-flex items-center text-xs font-medium text-slate-500 hover:text-slate-800 gap-1 transition-colors">
				<ArrowLeft className="w-3.5 h-3.5" /> Kembali ke Dashboard
			</Link>

			{/* Header */}
			<div className="bg-white border border-slate-200 rounded-xl p-6 shadow-xs">
				<div className="flex flex-col gap-3 lg:flex-row lg:items-start lg:justify-between">
					<div className="space-y-1">
						<h2 className="text-lg font-bold text-slate-900">Konfigurasi AI</h2>
					</div>
					<Link href="/settings/ai/create">
						<Button className="w-full sm:w-auto">
							<Plus className="w-4 h-4 mr-1.5" /> Tambah Konfigurasi
						</Button>
					</Link>
				</div>
			</div>

			{/* Config Cards */}
			{hasConfigs ? (
				<div className="grid grid-cols-1 lg:grid-cols-2 gap-4">
					{configurations.map(config => (
						<ConfigCard
							key={config.id}
							config={config}
							isOnly={configurations.length === 1}
						/>
					))}
				</div>
			) : (
				<div className="bg-white border border-slate-200 rounded-xl p-12 shadow-xs text-center">
					<div className="mx-auto w-12 h-12 rounded-full bg-slate-100 flex items-center justify-center mb-4">
						<BrainCircuit className="w-6 h-6 text-slate-400" />
					</div>
					<h3 className="text-sm font-bold text-slate-900 mb-1">Belum ada konfigurasi AI</h3>
					<p className="text-xs text-slate-500 mb-4">
						Tambahkan konfigurasi AI pertama untuk mulai menggunakan fitur analisis laporan keuangan.
					</p>
					<Link href="/settings/ai/create">
						
					</Link>
				</div>
			)}
		</div>
	);
}

Index.layout = page => <AppLayout title="Konfigurasi AI" children={page} />;
