import NeracaForm from "./NeracaForm";
import LabaRugiForm from "./LabaRugiForm";
import ArusKasForm from "./ArusKasForm";

export default function ExtractionForm({ data, foundAt, onDataChange, onMetadataChange, onCashFlowComponentChange, disabled }) {
    return (
        <div className="grid grid-cols-1 gap-5">
            <NeracaForm
                data={data}
                foundAt={foundAt}
                onDataChange={onDataChange}
                onMetadataChange={onMetadataChange}
                disabled={disabled}
            />

            <LabaRugiForm
                data={data}
                foundAt={foundAt}
                onDataChange={onDataChange}
                onMetadataChange={onMetadataChange}
                disabled={disabled}
            />

            <ArusKasForm
                data={data}
                foundAt={foundAt}
                onDataChange={onDataChange}
                onMetadataChange={onMetadataChange}
                onCashFlowComponentChange={onCashFlowComponentChange}
                disabled={disabled}
            />
        </div>
    );
}
