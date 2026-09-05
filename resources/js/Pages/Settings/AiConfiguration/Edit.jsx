import { useForm, Link } from "@inertiajs/react";
import AppLayout from "@/Layouts/AppLayout";
import { Button } from "@/Components/ui/button";
import {
	ArrowLeft,
	BrainCircuit,
	Loader2,
	Save,
	Plus,
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

function Field({ label, htmlFor, children, hint, required = false, error }) {
	return (
		<div className="flex flex-col gap-1.5">
			<label className="text-xs font-semibold text-slate-700" htmlFor={htmlFor}>
				{label} {required && <span className="text-red-500">*</span>}
			</label>
			{children}
			{hint && <p className="text-[11px] text-slate-500">{hint}</p>}
			{error && <p className="text-xs text-red-500">{error}</p>}
		</div>
	);
}

export default function Edit({ configuration, nextPriority, availablePriorities = [], mode }) {
	const isEdit = mode === "edit";

	const baseOptions = availablePriorities && availablePriorities.length > 0
		? availablePriorities
		: [1];
	const priorityOptions = configuration?.priority && !baseOptions.includes(configuration.priority)
		? [...baseOptions, configuration.priority].sort((a, b) => a - b)
		: baseOptions;

	const defaultPriority = configuration?.priority ?? nextPriority ?? priorityOptions[priorityOptions.length - 1];

	const { data, setData, put, post, processing, errors } = useForm({
		name: configuration?.name ?? "",
		llm_provider: configuration?.llm_provider ?? "gemini",
		base_url: configuration?.base_url ?? "",
		llm_model: configuration?.llm_model ?? "",
		llm_api_key: "",
		priority: defaultPriority,
	});

	function handleSubmit(e) {
		e.preventDefault();
		if (isEdit) {
			put(`/settings/ai/${configuration.id}`);
		} else {
			post("/settings/ai");
		}
	}

	const isOllama = data.llm_provider === "ollama";

	return (
		<div className="max-w-5xl mx-auto space-y-4">
			<Link href="/settings/ai" className="inline-flex items-center text-xs font-medium text-slate-500 hover:text-slate-800 gap-1 transition-colors">
				<ArrowLeft className="w-3.5 h-3.5" /> Kembali
			</Link>

			<form onSubmit={handleSubmit} className="space-y-5">
				{/* Section: Identitas */}
				<SectionCard
					icon={BrainCircuit}
					title={isEdit ? "Edit Konfigurasi" : "Tambah Konfigurasi Baru"}
					description={isEdit
						? "Ubah pengaturan provider, model, dan API key untuk konfigurasi ini."
						: "Buat konfigurasi AI baru. Konfigurasi pertama akan otomatis aktif."
					}
				>
					<div className="grid grid-cols-1 md:grid-cols-2 gap-4">
						<Field label="Nama Konfigurasi" htmlFor="name" required hint="Label untuk identifikasi (misal: Gemini Utama, OpenAI Backup)." error={errors.name}>
							<input
								id="name"
								type="text"
								value={data.name}
								onChange={e => setData("name", e.target.value)}
								placeholder="misal: Gemini Utama"
								className="px-3 py-2 text-sm border border-slate-200 rounded-md focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
								disabled={processing}
								autoComplete="off"
							/>
						</Field>

						<Field label="Prioritas" htmlFor="priority" required hint="Urutan failover (1 = prioritas paling utama)." error={errors.priority}>
							<select
								id="priority"
								value={data.priority}
								onChange={e => setData("priority", parseInt(e.target.value) || 1)}
								className="px-3 py-2 text-sm border border-slate-200 rounded-md focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 bg-white"
								disabled={processing}
							>
								{priorityOptions.map(p => (
									<option key={p} value={p}>
										Prioritas #{p} {p === 1 ? "(Utama)" : `(Cadangan ${p - 1})`}
									</option>
								))}
							</select>
						</Field>

						<Field label="Provider" htmlFor="llm_provider" required hint="Pilih provider LLM yang ingin digunakan." error={errors.llm_provider}>
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
						</Field>

						<Field label="Model" htmlFor="llm_model" required hint="Nama model yang dipanggil backend (misal: gemini-2.0-flash, gpt-4o, llama3)." error={errors.llm_model}>
							<input
								id="llm_model"
								type="text"
								value={data.llm_model}
								onChange={e => setData("llm_model", e.target.value)}
								placeholder="misal: gemini-2.0-flash"
								className="px-3 py-2 text-sm border border-slate-200 rounded-md focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
								disabled={processing}
								autoComplete="new-password"
							/>
						</Field>

						{isOllama ? (
							<Field label="Base URL" htmlFor="base_url" required hint="Endpoint local server Ollama." error={errors.base_url}>
								<input
									id="base_url"
									type="text"
									value={data.base_url}
									onChange={e => setData("base_url", e.target.value)}
									placeholder="http://localhost:11434"
									className="px-3 py-2 text-sm border border-slate-200 rounded-md focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
									disabled={processing}
								/>
							</Field>

						) : (
							<Field
								label="API Key"
								htmlFor="llm_api_key"
								hint={isEdit && configuration?.has_api_key
									? "API Key tersimpan. Biarkan kosong jika tidak ingin mengubah."
									: "Masukkan API key provider."
								}
								error={errors.llm_api_key}
							>
								<input
									id="llm_api_key"
									type="password"
									value={data.llm_api_key}
									onChange={e => setData("llm_api_key", e.target.value)}
									placeholder={isEdit && configuration?.has_api_key ? "••••••••••••••••" : "Masukkan API Key"}
									className="px-3 py-2 text-sm border border-slate-200 rounded-md focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
									disabled={processing}
								/>
							</Field>
						)}
					</div>
				</SectionCard>

				{/* Submit */}
				<div className="flex flex-col sm:flex-row justify-end gap-2 pt-1">
					<Link href="/settings/ai">
						<Button type="button" variant="outline" disabled={processing} className="w-full sm:w-auto">
							Batal
						</Button>
					</Link>
					<Button type="submit" disabled={processing} className="w-full sm:w-auto min-w-[160px]">
						{processing ? (
							<><Loader2 className="w-4 h-4 animate-spin mr-1.5" /> Menyimpan</>
						) : isEdit ? (
							<><Save className="w-4 h-4 mr-1.5" /> Simpan Perubahan</>
						) : (
							<><Plus className="w-4 h-4 mr-1.5" /> Tambah Konfigurasi</>
						)}
					</Button>
				</div>
			</form>
		</div>
	);
}

Edit.layout = page => <AppLayout title="Konfigurasi AI" children={page} />;
