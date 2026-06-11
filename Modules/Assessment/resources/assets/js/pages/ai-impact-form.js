export function aiImpactForm() {
    return {
        baselineVal:  0,
        achievedVal:  0,
        investCost:   0,
        benefitVal:   0,

        get previewVisible() {
            return this.baselineVal > 0 && this.achievedVal > 0
        },
        get roiVisible() {
            return this.investCost > 0 && this.benefitVal > 0
        },
        get improvementLabel() {
            if (!this.baselineVal) return '—'
            const pct = ((this.achievedVal - this.baselineVal) / Math.abs(this.baselineVal)) * 100
            return (pct >= 0 ? '+' : '') + pct.toFixed(1) + '%'
        },
        get roiLabel() {
            if (!this.investCost) return '—'
            const roi = ((this.benefitVal - this.investCost) / this.investCost) * 100
            return roi.toFixed(1) + '% (' + (roi / 100).toFixed(2) + 'x)'
        },
    }
}
