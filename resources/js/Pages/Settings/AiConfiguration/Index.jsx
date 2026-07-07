import { Link } from "@inertiajs/react";
import AppLayout from "@/Layouts/AppLayout";
import { Button } from "@/Components/ui/button";
import { Badge } from "@/Components/ui/badge";
import {
	ArrowLeft,
	BrainCircuit,
	Database,
	Edit3,
	Layers3,
	Sparkles,
	Wand2,
	ShieldCheck,
	Server,
	KeyRound,
	FileCode2,
} from "lucide-react";

function SectionCard({ icon: Icon, title, description, children }) {
	return (
		<section className="bg-white border border-slate-200 rounded-xl shadow-xs overflow-hidden">
			<div className="flex items-start gap-3 p-5 border-b border-slate-100 bg-slate-50/70">
				<div className="p-2 rounded-lg bg-blue-50 border border-blue-100 text-blue-700">
					<Icon className="w-4 h-4" />
				</div>
				<div className="min-w-0">
					<h3 className="text-sm font-bold text-slate-900">{title}</h3>
					<p className="text-xs text-slate-500 mt-0.5">{description}</p>
				</div>
			</div>

			<div className="p-5 space-y-4">{children}</div>
		</section>
	);
}

function DetailRow({ label, value, mono = false, placeholder = "Belum diatur" }) {
	return (
		<div className="flex flex-col gap-1.5 sm:flex-row sm:items-start sm:justify-between sm:gap-6 border-b border-slate-100 pb-3 last:border-b-0 last:pb-0">
			<div className="text-xs font-semibold text-slate-500 uppercase tracking-wide">{label}</div>
			<div className={`text-sm text-slate-900 break-words ${mono ? "font-mono text-[13px]" : ""}`}>
				{value || <span className="text-slate-400">{placeholder}</span>}
			</div>
		</div>
	);
}

function Pill({ label, value }) {
	return (
		<div className="flex items-center justify-between gap-3 rounded-lg border border-slate-200 bg-slate-50 px-3 py-2">
			<span className="text-xs font-semibold text-slate-500 uppercase tracking-wide">{label}</span>
			<span className="text-sm font-semibold text-slate-900">{value || "-"}</span>
		</div>
	);
}

export default function Index({ configuration }) {
	const hasConfiguration = Boolean(configuration);

	return (
		<div className="max-w-5xl mx-auto space-y-4">
			<Link href="/dashboard" className="inline-flex items-center text-xs font-medium text-slate-500 hover:text-slate-800 gap-1 transition-colors">
				<ArrowLeft className="w-3.5 h-3.5" /> Kembali ke Dashboard
			</Link>

			<div className="bg-white border border-slate-200 rounded-xl p-6 shadow-xs space-y-2">
				<div className="flex flex-col gap-3 lg:flex-row lg:items-start lg:justify-between">
					<div className="space-y-2">
						{/* <div className="inline-flex items-center gap-2 px-2.5 py-1 rounded-full bg-blue-50 border border-blue-100 text-blue-700 text-[11px] font-semibold uppercase tracking-wide">
							<Sparkles className="w-3.5 h-3.5" /> AI Configuration
						</div> */}
						<div>
							<h2 className="text-lg font-bold text-slate-900">Konfigurasi AI</h2>
							<p className="text-xs text-slate-500 mt-0.5 max-w-2xl">
								Pengaturan provider, model, vector store, dan prompt sistem yang dipakai pipeline analisis.
							</p>
						</div>
					</div>
                    <div className="flex flex-col sm:flex-row justify-end gap-2 pt-1">
                                    <Link href="/settings/ai/edit">
                                        <Button variant="outline" className="w-full sm:w-auto">
                                            <Edit3 className="w-4 h-4 mr-1.5" /> Edit
                                        </Button>
                                    </Link>
                    </div>

					{/* <div className="flex items-center gap-2 self-start rounded-lg border border-emerald-100 bg-emerald-50 px-3 py-2 text-emerald-700">
						<ShieldCheck className="w-4 h-4" />
						<div className="text-xs">
							<p className="font-semibold">Status sistem</p>
							<p className="text-emerald-600/80">{hasConfiguration ? "Konfigurasi tersedia" : "Belum ada konfigurasi"}</p>
						</div>
					</div> */}
				</div>
			</div>

			<div className="grid grid-cols-1 md:grid-cols-3 gap-4">
				<Pill label="LLM" value={configuration?.llm_model} />
				<Pill label="Embedding" value={configuration?.embedding_model} />
				<Pill label="Reranker" value={configuration?.reranker_model} />
			</div>

			<SectionCard
				icon={BrainCircuit}
				title="LLM"
				description="Model utama yang digunakan untuk menghasilkan jawaban dan analisis naratif."
			>
				<DetailRow label="Provider" value={configuration?.llm_provider} />
				<DetailRow label="Model" value={configuration?.llm_model} />
				<DetailRow label="Base URL" value={configuration?.llm_url} mono />
				<DetailRow label="API Key" value={configuration?.llm_api_key ? "Tersimpan" : "Tidak ada"} />
			</SectionCard>

			<SectionCard
				icon={Database}
				title="Embedding"
				description="Konfigurasi embedding untuk indexing dan pencarian semantik."
			>
				<DetailRow label="Provider" value={configuration?.embedding_provider} />
				<DetailRow label="Model" value={configuration?.embedding_model} />
				<DetailRow label="Base URL" value={configuration?.embedding_url} mono />
				<DetailRow label="API Key" value={configuration?.embedding_api_key ? "Tersimpan" : "Tidak ada"} />
			</SectionCard>

			<SectionCard
				icon={Layers3}
				title="Reranker dan Vector Store"
				description="Bagian yang mengatur ranking ulang hasil retrieval dan storage vektor."
			>
				<div className="grid grid-cols-1 md:grid-cols-2 gap-4">
					<div className="space-y-3">
						<DetailRow label="Reranker Provider" value={configuration?.reranker_provider} />
						<DetailRow label="Reranker Model" value={configuration?.reranker_model} />
						<DetailRow label="Top N" value={configuration?.reranker_top_n} />
						<DetailRow label="Reranker API Key" value={configuration?.reranker_api_key ? "Tersimpan" : "Tidak ada"} />
					</div>
					<div className="space-y-3">
						<DetailRow label="LocalAI URL" value={configuration?.localai_url} mono />
						<DetailRow label="Vector Store Driver" value={configuration?.vector_store_driver} />
						<DetailRow label="Vector Store Name" value={configuration?.vector_store_name} />
						<DetailRow label="Vector Store Path" value={configuration?.vector_store_path} mono />
					</div>
				</div>
			</SectionCard>

			<SectionCard
				icon={Wand2}
				title="System Prompt"
				description="Instruksi global yang dipakai untuk mengarahkan gaya dan format output AI."
			>
				<div className="rounded-lg border border-slate-200 bg-slate-50 p-4">
					<p className="text-xs uppercase tracking-wide font-semibold text-slate-500 mb-2 flex items-center gap-2">
						<FileCode2 className="w-3.5 h-3.5" /> Prompt
					</p>
					<pre className="text-sm text-slate-800 whitespace-pre-wrap leading-6 font-mono">
						{configuration?.system_prompt || "Belum ada system prompt yang disimpan."}
					</pre>
				</div>
			</SectionCard>

			
		</div>
	);
}

Index.layout = page => <AppLayout title="Konfigurasi AI" children={page} />;