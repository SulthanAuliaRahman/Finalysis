import { useState } from "react";
import { Button } from "@/Components/ui/button";
import { Badge } from "@/Components/ui/badge";
import { ChevronLeft, ChevronRight } from "lucide-react";

export default function ChunkViewer({ chunks }) {
    // Setup Filter dan Navigasi Chunk
    const [filterType, setFilterType] = useState("");
    const [currentIndex, setCurrentIndex] = useState(0);

    // Ambil daftar tipe unik untuk dropdown filter
    const uniqueTypes = [...new Set(chunks.map(c => c.metadata.statement_type))];

    // Saring chunk berdasarkan pilihan dropdown
    const filteredChunks = filterType
        ? chunks.filter(c => c.metadata.statement_type === filterType)
        : chunks;

    const currentChunk = filteredChunks[currentIndex];

    function handleNext() {
        if (currentIndex < filteredChunks.length - 1) setCurrentIndex(currentIndex + 1);
    }

    function handlePrev() {
        if (currentIndex > 0) setCurrentIndex(currentIndex - 1);
    }

    return (
        <div className="bg-white border border-slate-200 rounded-xl overflow-hidden shadow-xs">
            {/* Toolbar Navigasi & Filter */}
            <div className="flex flex-wrap items-center gap-3 p-3 border-b border-slate-100 bg-slate-50/50">
                <div className="flex items-center gap-1.5">
                    <Button
                        variant="outline"
                        size="sm"
                        className="h-8 px-2 text-slate-600"
                        onClick={handlePrev}
                        disabled={currentIndex === 0}
                    >
                        <ChevronLeft className="w-4 h-4" /> Prev
                    </Button>
                    <Button
                        variant="outline"
                        size="sm"
                        className="h-8 px-2 text-slate-600"
                        onClick={handleNext}
                        disabled={filteredChunks.length === 0 || currentIndex === filteredChunks.length - 1}
                    >
                        Next <ChevronRight className="w-4 h-4" />
                    </Button>
                </div>

                <span className="text-xs font-medium text-slate-500 min-w-[50px] text-center font-mono">
                    {filteredChunks.length > 0 ? `${currentIndex + 1} / ${filteredChunks.length}` : "0 / 0"}
                </span>

                <div className="ml-auto flex items-center gap-2">
                    <span className="text-xs font-semibold text-slate-500">Filter Tipe:</span>
                    <select
                        className="text-xs border border-slate-200 rounded bg-white px-2 py-1.5 focus:outline-none focus:border-blue-500"
                        value={filterType}
                        onChange={(e) => {
                            setFilterType(e.target.value);
                            setCurrentIndex(0); // Reset index tiap ganti filter
                        }}
                    >
                        <option value="">Semua Tipe</option>
                        {uniqueTypes.map(type => (
                            <option key={type} value={type}>{type.replace('_', ' ').toUpperCase()}</option>
                        ))}
                    </select>
                </div>
            </div>

            {/* Body Viewer (Teks Chunk) */}
            <div className="p-5 bg-slate-900 text-slate-300 font-mono text-sm leading-relaxed whitespace-pre-wrap max-h-[800px] overflow-y-auto">
                {currentChunk ? currentChunk.text : <span className="text-slate-600 italic">Tidak ada chunk yang sesuai filter.</span>}
            </div>

            {/* Footer Metadata */}
            {currentChunk && (
                <div className="flex flex-wrap gap-x-6 gap-y-2 p-4 border-t border-slate-100 bg-slate-50 text-xs">
                    <div className="flex gap-1.5 items-center">
                        <span className="font-semibold text-slate-400">Tipe Tabel:</span>
                        <Badge variant="outline" className="text-[10px] bg-white text-blue-700 border-blue-200 capitalize">
                            {currentChunk.metadata.statement_type.replace('_', ' ')}
                        </Badge>
                    </div>
                    <div className="flex gap-1.5 items-center">
                        <span className="font-semibold text-slate-400">Chunk ID:</span>
                        <span className="text-slate-700 font-mono font-medium">#{currentChunk.metadata.chunk_index}</span>
                    </div>
                    <div className="flex gap-1.5 items-center">
                        <span className="font-semibold text-slate-400">Halaman PDF:</span>
                        <span className="text-slate-700 font-mono font-medium">
                            {currentChunk.metadata.page_start} {currentChunk.metadata.page_start !== currentChunk.metadata.page_end && `- ${currentChunk.metadata.page_end}`}
                        </span>
                    </div>
                    {/* Render periode hanya jika ada datanya di metadata */}
                    {currentChunk.metadata.period && (
                        <div className="flex gap-1.5 items-center">
                            <span className="font-semibold text-slate-400">Periode:</span>
                            <span className="text-slate-700 font-mono font-medium">{currentChunk.metadata.period}</span>
                        </div>
                    )}
                </div>
            )}
        </div>
    );
}
