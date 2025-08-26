#!/bin/bash

# Script de rollback pour le fix des prix
# Usage: ./rollback-price-fix.sh

echo "🚨 ROLLBACK: Retour à la version stable avant le fix des prix"
echo "============================================================="

# Vérifier qu'on est sur main
CURRENT_BRANCH=$(git branch --show-current)
if [ "$CURRENT_BRANCH" != "main" ]; then
    echo "❌ Erreur: Vous devez être sur la branche main"
    exit 1
fi

# Demander confirmation
read -p "⚠️  Êtes-vous sûr de vouloir revenir à la version stable? (y/N): " -n 1 -r
echo
if [[ ! $REPLY =~ ^[Yy]$ ]]; then
    echo "❌ Rollback annulé"
    exit 1
fi

echo "📋 Rollback en cours..."

# Reset vers le tag stable
git reset --hard v1.0-stable-before-price-fix

echo "✅ Rollback terminé vers la version stable"
echo ""
echo "🔄 Pour déployer le rollback sur GitHub/Forge:"
echo "   git push origin main --force"
echo ""
echo "⚠️  Attention: Cela écrasera les modifications sur GitHub"
echo "   Les modifications du fix seront toujours disponibles dans"
echo "   la branche 'feature/fix-price-ordering'"
