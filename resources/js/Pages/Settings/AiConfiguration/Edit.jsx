import { useForm, Link } from "@inertiajs/react";
import AppLayout from "@/Layouts/AppLayout";
import { Button } from "@/Components/ui/button";
import {
	ArrowLeft,
	BrainCircuit,
	Database,
	Layers3,
	Loader2,
	Save,
	ShieldCheck,
	Sparkles,
	Wand2,
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

function Field({ label, htmlFor, children, hint, required = false }) {
	return (
		<div className="flex flex-col gap-1.5">
			<label className="text-xs font-semibold text-slate-700" htmlFor={htmlFor}>
				{label} {required && <span className="text-red-500">*</span>}
			</label>
			{children}
			{hint && <p className="text-[11px] text-slate-500">{hint}</p>}
		</div>
	);
}

export default function Edit({ configuration }) {
	const { data, setData, put, processing, errors } = useForm({
		llm_provider: configuration?.llm_provider ?? "gemini",
		base_url: configuration?.base_url ?? "",
		llm_model: configuration?.llm_model ?? "",
		llm_api_key: "",
	});

	function handleSubmit(e) {
		e.preventDefault();
		put("/settings/ai");
	}

	const isOllama = data.llm_provider === "ollama";

	return (
		<div className="max-w-5xl mx-auto space-y-4">
			<Link href="/settings/ai" className="inline-flex items-center text-xs font-medium text-slate-500 hover:text-slate-800 gap-1 transition-colors">
				<ArrowLeft className="w-3.5 h-3.5" /> Kembali
			</Link>

			<form onSubmit={handleSubmit} className="space-y-5" >
				<SectionCard
					icon={BrainCircuit}
					title="LLM"
					description="Pengaturan model utama untuk menjawab, menganalisis, dan menulis narasi hasil."
				>
					<div className="grid grid-cols-1 md:grid-cols-2 gap-4">
						<Field label="Provider" htmlFor="llm_provider" required hint="Pilih provider LLM yang ingin digunakan.">
							<select
								id="llm_provider"
								value={data.llm_provider}
								onChange={e => setData("llm_provider", e.target.value)}
								className="px-3 py-2 text-sm border border-slate-200 rounded-md focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 bg-white"
								disabled={processing}
							>
								<option value="gemini">Gemini (Google)</option>
								<option value="openai">OpenAI</option>
								<option value="anthropic">Anthropic (Claude)</option>
								<option value="ollama">Ollama (Local)</option>
							</select>
							{errors.llm_provider && <p className="text-xs text-red-500">{errors.llm_provider}</p>}
						</Field>

						<Field label="Model" htmlFor="llm_model" required hint="Nama model yang dipanggil backend (misal: gemini-1.5-pro, gpt-4o, llama3).">
							<input
								id="llm_model"
								type="text"
								value={data.llm_model}
								onChange={e => setData("llm_model", e.target.value)}
								placeholder="misal: gemini-1.5-pro"
								className="px-3 py-2 text-sm border border-slate-200 rounded-md focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
								disabled={processing}
								autoComplete="new-password"
							/>
							{errors.llm_model && <p className="text-xs text-red-500">{errors.llm_model}</p>}
						</Field>

						{isOllama ? (
							<Field label="Base URL" htmlFor="base_url" required hint="Endpoint local server Ollama.">
								<input
									id="base_url"
									type="text"
									value={data.base_url}
									onChange={e => setData("base_url", e.target.value)}
									placeholder="http://localhost:11434"
									className="px-3 py-2 text-sm border border-slate-200 rounded-md focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
									disabled={processing}
								/>
								{errors.base_url && <p className="text-xs text-red-500">{errors.base_url}</p>}
							</Field>
						) : (
							<Field
								label="API Key"
								htmlFor="llm_api_key"
								hint={configuration?.has_api_key ? "API Key tersimpan. Biarkan kosong jika tidak ingin mengubah." : "Masukkan API key provider."}
							>
								<input
									id="llm_api_key"
									type="password"
									value={data.llm_api_key}
									onChange={e => setData("llm_api_key", e.target.value)}
									placeholder={configuration?.has_api_key ? "••••••••••••••••" : "Masukkan API Key"}
									className="px-3 py-2 text-sm border border-slate-200 rounded-md focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
									disabled={processing}
								/>
								{errors.llm_api_key && <p className="text-xs text-red-500">{errors.llm_api_key}</p>}
							</Field>
						)}
					</div>
				</SectionCard>
{/* 
				<SectionCard
					icon={Database}
					title="Embedding"
					description="Pengaturan model embedding untuk chunking, indexing, dan pencarian semantik."
				>
					<div className="grid grid-cols-1 md:grid-cols-2 gap-4">
						<Field label="Provider" htmlFor="embedding_provider" required>
							<input
								id="embedding_provider"
								type="text"
								value={data.embedding_provider}
								onChange={e => setData("embedding_provider", e.target.value)}
								className="px-3 py-2 text-sm border border-slate-200 rounded-md focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
								disabled={processing}
							/>
							{errors.embedding_provider && <p className="text-xs text-red-500">{errors.embedding_provider}</p>}
						</Field>

						<Field label="Model" htmlFor="embedding_model" required>
							<input
								id="embedding_model"
								type="text"
								value={data.embedding_model}
								onChange={e => setData("embedding_model", e.target.value)}
								className="px-3 py-2 text-sm border border-slate-200 rounded-md focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
								disabled={processing}
							/>
							{errors.embedding_model && <p className="text-xs text-red-500">{errors.embedding_model}</p>}
						</Field>

						<Field label="Base URL" htmlFor="embedding_url" hint="Biasanya kosong jika memakai provider bawaan.">
							<input
								id="embedding_url"
								type="text"
								value={data.embedding_url}
								onChange={e => setData("embedding_url", e.target.value)}
								className="px-3 py-2 text-sm border border-slate-200 rounded-md focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
								disabled={processing}
							/>
							{errors.embedding_url && <p className="text-xs text-red-500">{errors.embedding_url}</p>}
						</Field>

						<Field label="API Key" htmlFor="embedding_api_key">
							<input
								id="embedding_api_key"
								type="password"
								value={data.embedding_api_key}
								onChange={e => setData("embedding_api_key", e.target.value)}
								className="px-3 py-2 text-sm border border-slate-200 rounded-md focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
								disabled={processing}
							/>
							{errors.embedding_api_key && <p className="text-xs text-red-500">{errors.embedding_api_key}</p>}
						</Field>
					</div>
				</SectionCard> */}
{/* 
				<SectionCard
					icon={Layers3}
					title="Reranker dan Vector Store"
					description="Atur komponen ranking ulang dan penyimpanan vektor yang dipakai saat retrieval."
				>
					<div className="grid grid-cols-1 md:grid-cols-2 gap-4">
						<Field label="Reranker Provider" htmlFor="reranker_provider" required>
							<input
								id="reranker_provider"
								type="text"
								value={data.reranker_provider}
								onChange={e => setData("reranker_provider", e.target.value)}
								className="px-3 py-2 text-sm border border-slate-200 rounded-md focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
								disabled={processing}
							/>
							{errors.reranker_provider && <p className="text-xs text-red-500">{errors.reranker_provider}</p>}
						</Field>

						<Field label="Reranker Model" htmlFor="reranker_model" required>
							<input
								id="reranker_model"
								type="text"
								value={data.reranker_model}
								onChange={e => setData("reranker_model", e.target.value)}
								className="px-3 py-2 text-sm border border-slate-200 rounded-md focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
								disabled={processing}
							/>
							{errors.reranker_model && <p className="text-xs text-red-500">{errors.reranker_model}</p>}
						</Field>

						<Field label="Top N" htmlFor="reranker_top_n" hint="Jumlah kandidat hasil rerank yang dipilih.">
							<input
								id="reranker_top_n"
								type="number"
								min="1"
								max="20"
								value={data.reranker_top_n}
								onChange={e => setData("reranker_top_n", e.target.value)}
								className="px-3 py-2 text-sm border border-slate-200 rounded-md focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
								disabled={processing}
							/>
							{errors.reranker_top_n && <p className="text-xs text-red-500">{errors.reranker_top_n}</p>}
						</Field>

						<Field label="Reranker API Key" htmlFor="reranker_api_key">
							<input
								id="reranker_api_key"
								type="password"
								value={data.reranker_api_key}
								onChange={e => setData("reranker_api_key", e.target.value)}
								className="px-3 py-2 text-sm border border-slate-200 rounded-md focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
								disabled={processing}
							/>
							{errors.reranker_api_key && <p className="text-xs text-red-500">{errors.reranker_api_key}</p>}
						</Field>

						<Field label="LocalAI URL" htmlFor="localai_url" hint="Dipakai jika ada endpoint localai khusus.">
							<input
								id="localai_url"
								type="text"
								value={data.localai_url}
								onChange={e => setData("localai_url", e.target.value)}
								className="px-3 py-2 text-sm border border-slate-200 rounded-md focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
								disabled={processing}
							/>
							{errors.localai_url && <p className="text-xs text-red-500">{errors.localai_url}</p>}
						</Field>

						<Field label="Vector Store Driver" htmlFor="vector_store_driver" required>
							<input
								id="vector_store_driver"
								type="text"
								value={data.vector_store_driver}
								onChange={e => setData("vector_store_driver", e.target.value)}
								className="px-3 py-2 text-sm border border-slate-200 rounded-md focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
								disabled={processing}
							/>
							{errors.vector_store_driver && <p className="text-xs text-red-500">{errors.vector_store_driver}</p>}
						</Field>

						<Field label="Vector Store Name" htmlFor="vector_store_name" required>
							<input
								id="vector_store_name"
								type="text"
								value={data.vector_store_name}
								onChange={e => setData("vector_store_name", e.target.value)}
								className="px-3 py-2 text-sm border border-slate-200 rounded-md focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
								disabled={processing}
							/>
							{errors.vector_store_name && <p className="text-xs text-red-500">{errors.vector_store_name}</p>}
						</Field>

						<Field label="Vector Store Path" htmlFor="vector_store_path" hint="Opsional, relevan bila driver berbasis file.">
							<input
								id="vector_store_path"
								type="text"
								value={data.vector_store_path}
								onChange={e => setData("vector_store_path", e.target.value)}
								className="px-3 py-2 text-sm border border-slate-200 rounded-md focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
								disabled={processing}
							/>
							{errors.vector_store_path && <p className="text-xs text-red-500">{errors.vector_store_path}</p>}
						</Field>
					</div>
				</SectionCard> */}
{/* 
				<SectionCard
					icon={Wand2}
					title="System Prompt"
					description="Prompt dasar yang dipakai untuk mengarahkan gaya analisis AI."
				>
					<Field
						label="Instruksi Sistem"
						htmlFor="system_prompt"
						hint="Tulis kebijakan, format jawaban, dan preferensi output utama di sini."
					>
						<textarea
							id="system_prompt"
							value={data.system_prompt}
							onChange={e => setData("system_prompt", e.target.value)}
							rows={10}
							className="px-3 py-2 text-sm border border-slate-200 rounded-md focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 resize-y font-mono"
							disabled={processing}
						/>
						{errors.system_prompt && <p className="text-xs text-red-500">{errors.system_prompt}</p>}
					</Field>
				</SectionCard> */}

				<div className="flex flex-col sm:flex-row justify-end gap-2 pt-1">
					<Link href="/dashboard">
						<Button type="button" variant="outline" disabled={processing} className="w-full sm:w-auto">
							Batal
						</Button>
					</Link>
					<Button type="submit" disabled={processing} className="w-full sm:w-auto min-w-[140px]">
						{processing ? (
							<><Loader2 className="w-4 h-4 animate-spin mr-1.5" /> Menyimpan</>
						) : (
							<><Save className="w-4 h-4 mr-1.5" /> Simpan Konfigurasi</>
						)}
					</Button>
				</div>
			</form>
		</div>
	);
}

Edit.layout = page => <AppLayout title="AI Configuration" children={page} />;
