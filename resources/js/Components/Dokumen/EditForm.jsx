import EditNeracaForm from "./EditNeracaForm";
import EditLabaRugiForm from "./EditLabaRugiForm";
import EditArusKasForm from "./EditArusKasForm";

export default function EditForm({
    data,
    onDataChange,
    onCashFlowComponentChange,
    disabled,
    statementTypes = []
}) {
    const hasNeraca = statementTypes.includes("neraca");
    const hasLabaRugi = statementTypes.includes("laba_rugi");
    const hasArusKas = statementTypes.includes("arus_kas");

    return (
        <div className="grid grid-cols-1 gap-5">
            {hasNeraca && (
                <EditNeracaForm
                    data={data}
                    onDataChange={onDataChange}
                    disabled={disabled}
                />
            )}

            {hasLabaRugi && (
                <EditLabaRugiForm
                    data={data}
                    onDataChange={onDataChange}
                    disabled={disabled}
                />
            )}

            {hasArusKas && (
                <EditArusKasForm
                    data={data}
                    onDataChange={onDataChange}
                    onCashFlowComponentChange={onCashFlowComponentChange}
                    disabled={disabled}
                />
            )}
        </div>
    );
}
