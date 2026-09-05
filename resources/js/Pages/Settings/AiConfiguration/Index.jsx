import { Link, router } from "@inertiajs/react";
import AppLayout from "@/Layouts/AppLayout";
import { Button } from "@/Components/ui/button";
import { Badge } from "@/Components/ui/badge";
import {
	ArrowLeft,
	BrainCircuit,
	Plus,
	Edit3,
	Trash2,
	Power,
	RotateCcw,
	AlertTriangle,
	CheckCircle2,
	Clock,
	Server,
	KeyRound,
	ArrowUpDown,
} from "lucide-react";
import { useState } from "react";

function StatusBadge({ config }) {
	if (config.is_active && !config.is_currently_limited) {
		return (
			<span className="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-emerald-50 border border-emerald-200 text-emerald-700 text-[11px] font-semibold uppercase tracking-wide">
				<CheckCircle2 className="w-3 h-3" /> Aktif
			</span>
		);
	}

	if (config.is_currently_limited) {
		return (
			<span className="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-red-50 border border-red-200 text-red-700 text-[11px] font-semibold uppercase tracking-wide">
				<AlertTriangle className="w-3 h-3" /> Rate Limited
			</span>
		);
	}

	return (
		<span className="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-slate-50 border border-slate-200 text-slate-500 text-[11px] font-semibold uppercase tracking-wide">
			<Clock className="w-3 h-3" /> Standby
		</span>
	);
}

function ProviderBadge({ provider }) {
	const colorMap = {
		gemini: "bg-blue-50 text-blue-700 border-blue-200",
		openai: "bg-emerald-50 text-emerald-700 border-emerald-200",
		anthropic: "bg-amber-50 text-amber-700 border-amber-200",
		ollama: "bg-purple-50 text-purple-700 border-purple-200",
	};

	const labelMap = {
		gemini: "Gemini",
		openai: "OpenAI",
		anthropic: "Anthropic",
		ollama: "Ollama",
	};

	const color = colorMap[provider] ?? "bg-slate-50 text-slate-600 border-slate-200";
	const label = labelMap[provider] ?? provider;

	return (
		<span className={`inline-flex items-center px-2 py-0.5 rounded-md border text-[11px] font-semibold uppercase tracking-wide ${color}`}>
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

	function handleResetLimit() {
		router.post(`/settings/ai/${config.id}/reset-limit`, {}, {
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

	const cardBorder = config.is_active && !config.is_currently_limited
		? "border-emerald-200 ring-1 ring-emerald-100"
		: config.is_currently_limited
			? "border-red-200 ring-1 ring-red-100"
			: "border-slate-200";

	return (
		<div className={`bg-white border rounded-xl shadow-xs overflow-hidden transition-all ${cardBorder}`}>
			{/* Header */}
			<div className="flex items-start gap-3 p-5 border-b border-slate-100 bg-slate-50/70">
				<div className={`p-2 rounded-lg border ${
					config.is_active && !config.is_currently_limited
						? "bg-emerald-50 border-emerald-100 text-emerald-700"
						: config.is_currently_limited
							? "bg-red-50 border-red-100 text-red-700"
							: "bg-blue-50 border-blue-100 text-blue-700"
				}`}>
					<BrainCircuit className="w-4 h-4" />
				</div>
				<div className="min-w-0 flex-1">
					<div className="flex items-center gap-2 flex-wrap">
						<h3 className="text-sm font-bold text-slate-900">{config.name}</h3>
						<StatusBadge config={config} />
					</div>
					<div className="flex items-center gap-2 mt-1.5">
						<ProviderBadge provider={config.llm_provider} />
						<span className="text-xs text-slate-500 font-mono">{config.llm_model}</span>
					</div>
				</div>
				<div className="shrink-0 flex items-center gap-1 text-xs text-slate-500 font-semibold bg-slate-100 rounded-md px-2.5 py-1">
					<ArrowUpDown className="w-3 h-3" />
					<span>Prioritas #{config.priority}</span>
				</div>
			</div>

			{/* Details */}
			<div className="p-5 space-y-3">
				<div className="grid grid-cols-2 gap-3 text-xs">
					<div>
						<span className="font-semibold text-slate-500 uppercase tracking-wide block mb-0.5">Provider</span>
						<span className="text-slate-900">{config.llm_provider}</span>
					</div>
					<div>
						<span className="font-semibold text-slate-500 uppercase tracking-wide block mb-0.5">Model</span>
						<span className="text-slate-900 font-mono text-[13px]">{config.llm_model}</span>
					</div>
					<div>
						<span className="font-semibold text-slate-500 uppercase tracking-wide block mb-0.5">
							{config.llm_provider === "ollama" ? "Base URL" : "API Key"}
						</span>
						<span className="text-slate-900">
							{config.llm_provider === "ollama"
								? (config.base_url || <span className="text-slate-400">Belum diatur</span>)
								: (config.llm_api_key ? "••••••••" : <span className="text-slate-400">Tidak ada</span>)
							}
						</span>
					</div>
					<div>
						<span className="font-semibold text-slate-500 uppercase tracking-wide block mb-0.5">Request</span>
						<span className="text-slate-900">{config.request_count ?? 0} request</span>
					</div>
				</div>

				{/* Rate Limit Info */}
				{config.is_currently_limited && config.limited_until && (
					<div className="flex items-center gap-2 rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-xs text-red-700">
						<AlertTriangle className="w-3.5 h-3.5 shrink-0" />
						<span>
							Rate limited sampai{" "}
							<strong>
								{new Date(config.limited_until).toLocaleString("id-ID", {
									hour: "2-digit",
									minute: "2-digit",
									day: "2-digit",
									month: "short",
								})}
							</strong>
						</span>
					</div>
				)}
			</div>

			{/* Actions */}
			<div className="flex items-center gap-2 px-5 py-3 border-t border-slate-100 bg-slate-50/50">
				{!config.is_active && (
					<Button
						variant="outline"
						size="sm"
						onClick={handleActivate}
						className="text-emerald-700 border-emerald-200 hover:bg-emerald-50"
					>
						<Power className="w-3.5 h-3.5 mr-1" /> Aktifkan
					</Button>
				)}

				{config.is_currently_limited && (
					<Button
						variant="outline"
						size="sm"
						onClick={handleResetLimit}
						className="text-amber-700 border-amber-200 hover:bg-amber-50"
					>
						<RotateCcw className="w-3.5 h-3.5 mr-1" /> Reset Limit
					</Button>
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
						<p className="text-xs text-slate-500 max-w-2xl">
							Kelola beberapa provider dan API key. Hanya satu konfigurasi yang aktif pada satu waktu.
							Saat API kena rate limit, sistem otomatis berpindah ke konfigurasi cadangan berdasarkan urutan prioritas.
						</p>
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
						Tambahkan konfigurasi AI pertama untuk mulai menggunakan fitur analisis.
					</p>
					<Link href="/settings/ai/create">
						<Button>
							<Plus className="w-4 h-4 mr-1.5" /> Tambah Konfigurasi
						</Button>
					</Link>
				</div>
			)}

			{/* Info Box */}
			{hasConfigs && (
				<div className="bg-blue-50 border border-blue-200 rounded-xl p-4">
					<div className="flex items-start gap-3">
						<div className="p-1.5 rounded-md bg-blue-100 text-blue-700 shrink-0 mt-0.5">
							<ArrowUpDown className="w-3.5 h-3.5" />
						</div>
						<div className="text-xs text-blue-800 space-y-1">
							<p className="font-semibold">Cara kerja Auto-Switch</p>
							<p className="text-blue-700">
								Jika API yang aktif kena rate limit (HTTP 429), sistem otomatis berpindah ke konfigurasi cadangan
								berdasarkan nomor prioritas (kecil = lebih tinggi). Konfigurasi yang terkena limit akan
								otomatis pulih setelah 60 menit, atau bisa direset manual.
							</p>
						</div>
					</div>
				</div>
			)}
		</div>
	);
}

Index.layout = page => <AppLayout title="Konfigurasi AI" children={page} />;
